<?php

namespace App\Console\Commands;

use App\Services\ClinicalReminderService;
use Illuminate\Console\Command;

class SendClinicalReminders extends Command
{
    protected $signature = 'clinica:avisos {--enviar : Enviar usando WhatsApp si está habilitado y configurado}';
    protected $description = 'Previsualiza o envía recordatorios de citas y saludos de cumple-mes.';

    public function handle(ClinicalReminderService $service): int
    {
        $candidates = $service->candidates();
        $this->info('Avisos elegibles para hoy: '.count($candidates));
        if (!$this->option('enviar')) {
            $this->comment('Modo de vista previa: no se creó ningún envío ni se contactó a pacientes.');
            return self::SUCCESS;
        }
        if (!config('services.meta_whatsapp.clinical_automatic')) {
            $this->error('Envío desactivado. Define CLINICA_WHATSAPP_AUTOMATICO=true para habilitarlo.');
            return self::FAILURE;
        }
        $missing = collect([
            'META_AUTH_TOKEN' => config('services.meta_whatsapp.token'),
            'META_WHATSAPP_PHONE_NUMBER_ID' => config('services.meta_whatsapp.phone_number_id'),
            'META_TEMPLATE_RECORDATORIO_CITA' => config('services.meta_whatsapp.appointment_template'),
            'META_TEMPLATE_CUMPLEMES' => config('services.meta_whatsapp.birthday_template'),
        ])->filter(fn ($value) => blank($value))->keys();
        if ($missing->isNotEmpty()) {
            $this->error('Falta configuración requerida: '.$missing->implode(', '));
            return self::FAILURE;
        }
        foreach ($candidates as $candidate) {
            $notice = $service->send($candidate);
            $this->line($notice->tipo.' '.$notice->clave.': '.$notice->estado);
            usleep(250000);
        }
        return self::SUCCESS;
    }
}
