<?php

namespace Database\Seeders;

use App\Models\CitaClinica;
use App\Models\HorarioProfesionalClinico;
use App\Models\PacienteClinico;
use App\Models\Persona;
use App\Models\Personamaestro;
use App\Models\ProfesionalClinico;
use App\Models\ServicioClinico;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClinicaDemoSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::with('persona')->where('name', 'CAJADEMO')->firstOrFail();
        $branchId = (int) ($user->persona?->sucursal_id ?? 0);
        $districtId = (int) ($user->persona?->distrito_id ?? 0);

        if (!$branchId || !$districtId) {
            throw new \RuntimeException('CAJADEMO debe tener una sucursal y distrito asignados para cargar los datos de prueba.');
        }

        DB::transaction(function () use ($branchId, $districtId): void {
            $patientSamples = [
                ['701000001', 'Lucía Fernanda', 'Demo Ramos', 'F', '2001-03-14', '999000101'],
                ['701000002', 'Mateo Andrés', 'Demo Salazar', 'M', '2019-08-22', '999000102'],
                ['701000003', 'Camila Isabel', 'Demo Torres', 'F', '1994-11-09', '999000103'],
                ['701000004', 'Nicolás Javier', 'Demo Paredes', 'M', '1988-05-30', '999000104'],
                ['701000005', 'Valentina Sofía', 'Demo León', 'F', '2022-01-17', '999000105'],
            ];

            $patients = [];
            foreach ($patientSamples as [$document, $firstNames, $lastNames, $sex, $birthDate, $phone]) {
                $master = Personamaestro::firstOrCreate(
                    ['nrodoc' => $document],
                    ['tipopersona' => 'Natural', 'nombres' => $firstNames, 'apellidos' => $lastNames, 'sexo' => $sex, 'fechanac' => $birthDate]
                );
                $person = Persona::firstOrCreate(
                    ['personamaestro_id' => $master->id, 'sucursal_id' => $branchId],
                    [
                        'nombres' => $firstNames,
                        'apellidos' => $lastNames,
                        'direccion' => 'Dirección de prueba PULSO',
                        'email' => 'demo.patient.' . $document . '@example.test',
                        'telefonofijo' => '074000000',
                        'telefonomovil' => $phone,
                        'imagen' => '-',
                        'compartido' => 'N',
                        'no_grato' => false,
                        'distrito_id' => $districtId,
                    ]
                );
                DB::table('rolpersona')->updateOrInsert(
                    ['persona_id' => $person->id, 'rol_id' => 3, 'sucursal_id' => $branchId],
                    ['created_at' => now(), 'updated_at' => now()]
                );

                $patients[] = PacienteClinico::updateOrCreate(
                    ['persona_id' => $person->id],
                    [
                        'sucursal_id' => $branchId,
                        'tipo_documento' => 'DNI',
                        'numero_documento' => $document,
                        'nombres' => $firstNames,
                        'apellidos' => $lastNames,
                        'fecha_nacimiento' => $birthDate,
                        'sexo' => $sex,
                        'telefono' => $phone,
                        'email' => $person->email,
                        'direccion' => $person->direccion,
                        'tutor_nombres' => in_array($document, ['701000002', '701000005'], true) ? 'Andrea Salazar Demo' : null,
                        'tutor_documento' => in_array($document, ['701000002', '701000005'], true) ? '701000099' : null,
                        'tutor_telefono' => in_array($document, ['701000002', '701000005'], true) ? '999000199' : null,
                        'tutor_email' => null,
                        'observaciones' => 'DEMO PULSO: datos ficticios para pruebas.',
                        'acepta_notificaciones' => false,
                        'acepta_notificaciones_at' => null,
                    ]
                );
            }

            $professionalSamples = [
                ['709000001', 'Elena', 'Demo Medina', 'Pediatría', 'CMP-DEMO-001', '999000201'],
                ['709000002', 'Renato', 'Demo Campos', 'Medicina general', 'CMP-DEMO-002', '999000202'],
            ];
            $professionals = [];
            foreach ($professionalSamples as [$document, $firstNames, $lastNames, $specialty, $license, $phone]) {
                $professionals[] = ProfesionalClinico::firstOrCreate(
                    ['sucursal_id' => $branchId, 'numero_documento' => $document],
                    [
                        'tipo_documento' => 'DNI',
                        'nombres' => $firstNames,
                        'apellidos' => $lastNames,
                        'especialidad' => $specialty,
                        'registro_profesional' => $license,
                        'telefono' => $phone,
                        'email' => strtolower($firstNames) . '.demo@example.test',
                        'activo' => true,
                    ]
                );
            }

            $serviceSamples = [
                ['Consulta pediátrica DEMO', 'Evaluación pediátrica de prueba.', 45, 80.00],
                ['Control de crecimiento DEMO', 'Control de crecimiento y desarrollo.', 30, 60.00],
                ['Consulta medicina general DEMO', 'Consulta médica general de prueba.', 30, 70.00],
                ['Orientación nutricional DEMO', 'Orientación nutricional de prueba.', 45, 90.00],
            ];
            $services = [];
            foreach ($serviceSamples as [$name, $description, $duration, $price]) {
                $services[] = ServicioClinico::firstOrCreate(
                    ['sucursal_id' => $branchId, 'nombre' => $name],
                    ['descripcion' => $description, 'duracion_minutos' => $duration, 'precio' => $price, 'activo' => true]
                );
            }

            foreach ($professionals as $professional) {
                for ($day = 0; $day <= 6; $day++) {
                    HorarioProfesionalClinico::updateOrCreate(
                        ['sucursal_id' => $branchId, 'profesional_id' => $professional->id, 'dia_semana' => $day],
                        ['hora_inicio' => '08:00', 'hora_fin' => '18:00', 'activo' => true]
                    );
                }
            }

            $appointmentSlots = [
                [1, '09:00', 0, 0, 0, 'RESERVADA'],
                [1, '10:00', 1, 1, 1, 'CONFIRMADA'],
                [1, '11:00', 0, 2, 1, 'RESERVADA'],
                [2, '09:30', 1, 3, 2, 'CONFIRMADA'],
                [2, '10:30', 0, 4, 0, 'RESERVADA'],
                [3, '09:00', 1, 0, 2, 'RESERVADA'],
            ];

            foreach ($appointmentSlots as [$daysAhead, $time, $professionalIndex, $patientIndex, $serviceIndex, $status]) {
                $start = now()->addDays($daysAhead)->setTimeFromTimeString($time)->setSecond(0);
                $end = $start->copy()->addMinutes((int) $services[$serviceIndex]->duracion_minutos);
                CitaClinica::firstOrCreate(
                    [
                        'sucursal_id' => $branchId,
                        'profesional_id' => $professionals[$professionalIndex]->id,
                        'paciente_id' => $patients[$patientIndex]->id,
                        'inicio' => $start,
                    ],
                    [
                        'servicio_id' => $services[$serviceIndex]->id,
                        'fin' => $end,
                        'estado' => $status,
                        'motivo' => 'Cita de demostración PULSO',
                        'notas' => 'DEMO: información ficticia para pruebas.',
                    ]
                );
            }

            $historyStart = now()->subDay()->setTime(10, 0)->setSecond(0);
            CitaClinica::firstOrCreate(
                ['sucursal_id' => $branchId, 'profesional_id' => $professionals[0]->id, 'paciente_id' => $patients[2]->id, 'inicio' => $historyStart],
                [
                    'servicio_id' => $services[0]->id,
                    'fin' => $historyStart->copy()->addMinutes((int) $services[0]->duracion_minutos),
                    'estado' => 'ATENDIDA',
                    'motivo' => 'Control de demostración PULSO',
                    'notas' => 'DEMO: atención ficticia para validar reportes.',
                ]
            );
        });

        $this->command?->info('Datos clínicos DEMO insertados en la sucursal asignada a CAJADEMO.');
    }
}
