<?php

namespace App\Services;

use App\Models\CitaClinica;
use App\Models\NotificacionClinica;
use App\Models\PacienteClinico;
use App\Models\Sucursal;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class ClinicalReminderService
{
    public function candidates(?Carbon $day = null): array
    {
        $day ??= now()->startOfDay();
        $items = [];
        $appointments = CitaClinica::with(['paciente', 'profesional', 'servicio'])
            ->whereDate('inicio', $day->toDateString())
            ->whereIn('estado', ['RESERVADA', 'CONFIRMADA'])
            ->whereNull('deleted_at')->get();

        foreach ($appointments as $appointment) {
            $patient = $appointment->paciente;
            if (!$patient || !$patient->acepta_notificaciones) continue;
            $guardian = $patient->fecha_nacimiento && $patient->fecha_nacimiento->age < 18;
            $phone = $guardian ? ($patient->tutor_telefono ?: $patient->telefono) : $patient->telefono;
            if (!$phone) continue;
            $branchName = Sucursal::whereKey($appointment->sucursal_id)->value('nombrecomercial') ?: 'PULSO+';
            $items[] = [
                'key' => 'cita:'.$appointment->id.':'.$day->toDateString(), 'type' => 'CITA',
                'branch' => $appointment->sucursal_id, 'patient' => $patient->id, 'appointment' => $appointment->id,
                'date' => $day->toDateString(), 'phone' => $phone, 'template' => 'appointment_template',
                'variables' => [
                    $guardian ? ($patient->tutor_nombres ?: 'Familia') : $patient->nombres,
                    $appointment->servicio?->nombre ?: 'Atención',
                    $appointment->inicio->format('H:i'),
                    trim(($appointment->profesional?->nombres ?? '').' '.($appointment->profesional?->apellidos ?? '')),
                    $branchName,
                ],
            ];
        }

        $from = $day->copy()->subYear()->toDateString();
        $patients = PacienteClinico::whereNotNull('fecha_nacimiento')->where('fecha_nacimiento', '>', $from)->get();
        foreach ($patients as $patient) {
            $birth = Carbon::parse($patient->fecha_nacimiento);
            $scheduledDay = min($birth->day, $day->daysInMonth);
            if ($birth->day > $day->daysInMonth && $day->day !== $day->daysInMonth) continue;
            if ($birth->day <= $day->daysInMonth && $day->day !== $scheduledDay) continue;
            $guardianPhone = $patient->tutor_telefono ?: $patient->telefono;
            if (!$guardianPhone || !$patient->acepta_notificaciones) continue;
            $items[] = [
                'key' => 'cumplemes:'.$patient->id.':'.$day->toDateString(), 'type' => 'CUMPLEMES',
                'branch' => $patient->sucursal_id, 'patient' => $patient->id, 'appointment' => null,
                'date' => $day->toDateString(), 'phone' => $guardianPhone, 'template' => 'birthday_template',
                'variables' => [$patient->tutor_nombres ?: 'Familia', trim($patient->nombres.' '.$patient->apellidos), (string) $birth->diffInMonths($day)],
            ];
        }
        return $items;
    }

    public function send(array $candidate, bool $allowExplicitRetry = false): NotificacionClinica
    {
        $template = config('services.meta_whatsapp.'.$candidate['template']);
        $token = config('services.meta_whatsapp.token');
        $phoneNumberId = config('services.meta_whatsapp.phone_number_id');
        $now = now();
        [$notice, $claimed] = DB::transaction(function () use ($candidate, $now, $allowExplicitRetry) {
            $inserted = DB::table('notificacion_clinica')->insertOrIgnore([
                'clave' => $candidate['key'], 'sucursal_id' => $candidate['branch'],
                'paciente_id' => $candidate['patient'], 'cita_id' => $candidate['appointment'],
                'tipo' => $candidate['type'], 'fecha_evento' => $candidate['date'],
                'telefono' => $this->normalizePhone($candidate['phone']), 'estado' => 'PENDIENTE',
                'created_at' => $now, 'updated_at' => $now,
            ]);
            $notice = NotificacionClinica::where('clave', $candidate['key'])->lockForUpdate()->firstOrFail();

            if (!$inserted && $notice->estado === 'PENDIENTE') {
                // PENDIENTE proviene de la implementación previa y pudo quedar en ese
                // estado después de llamar al proveedor; no se reenvía a ciegas.
                $notice->update(['estado' => 'INCIERTO', 'error' => 'Estado previo a la protección contra duplicados. Verifica el envío en WhatsApp antes de continuar.']);
                return [$notice->fresh(), false];
            }
            if (!$inserted && in_array($notice->estado, ['ENVIADO', 'ENVIANDO', 'INCIERTO'], true)) {
                return [$notice, false];
            }
            if (!$inserted && $notice->estado === 'FALLIDO' && !$allowExplicitRetry) {
                return [$notice, false];
            }

            $notice->fill([
                'sucursal_id' => $candidate['branch'], 'paciente_id' => $candidate['patient'],
                'cita_id' => $candidate['appointment'], 'tipo' => $candidate['type'],
                'fecha_evento' => $candidate['date'], 'telefono' => $this->normalizePhone($candidate['phone']),
                'estado' => 'ENVIANDO', 'error' => null, 'respuesta' => null, 'enviado_at' => null,
            ])->save();
            return [$notice, true];
        });

        if (!$claimed) return $notice;

        if (!$token || !$phoneNumberId || !$template) {
            $notice->update(['estado' => 'FALLIDO', 'error' => 'Falta configurar token, número de WhatsApp o plantilla aprobada.']);
            return $notice;
        }

        $body = array_map(fn ($value) => ['type' => 'text', 'text' => (string) $value], $candidate['variables']);
        try {
            $response = Http::withToken($token)->acceptJson()->timeout(20)
                ->post('https://graph.facebook.com/'.config('services.meta_whatsapp.api_version').'/'.$phoneNumberId.'/messages', [
                    'messaging_product' => 'whatsapp', 'to' => $notice->telefono, 'type' => 'template',
                    'template' => ['name' => $template, 'language' => ['code' => 'es'], 'components' => [
                        ['type' => 'body', 'parameters' => $body],
                    ]],
                ]);
        } catch (\Throwable $e) {
            $notice->update([
                'estado' => 'INCIERTO',
                'error' => mb_substr('WhatsApp no confirmó si recibió el aviso. Verifica el proveedor antes de reintentar. '.$e->getMessage(), 0, 2000),
            ]);
            return $notice->fresh();
        }
        $result = $response->json() ?: ['http_status' => $response->status()];
        $accepted = $response->successful() && !empty($result['messages'][0]['id']);
        $explicitReject = $response->clientError() && !empty($result['error']);
        $notice->update([
            'estado' => $accepted ? 'ENVIADO' : ($explicitReject ? 'FALLIDO' : 'INCIERTO'), 'respuesta' => $result,
            'error' => $accepted ? null : mb_substr((string) ($result['error']['message'] ?? 'WhatsApp no aceptó el mensaje.'), 0, 2000),
            'enviado_at' => $accepted ? now() : null,
        ]);
        return $notice;
    }

    private function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone);
        if (!str_starts_with($digits, '51')) $digits = '51'.ltrim($digits, '0');
        return $digits;
    }
}
