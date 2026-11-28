<?php

namespace App\Http\Controllers;

use App\Models\CitaClinica;
use App\Models\HorarioProfesionalClinico;
use App\Models\NotificacionClinica;
use App\Models\Persona;
use App\Models\PacienteClinico;
use App\Models\Personamaestro;
use App\Models\ProfesionalClinico;
use App\Models\Perfile;
use App\Models\Menuopcione;
use App\Models\Rol;
use App\Models\Sucursal;
use App\Models\ServicioClinico;
use App\Models\Ubigeo;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Services\ClinicalReminderService;

class ClinicaController extends Controller
{
    private function sucursal(Request $request): int
    {
        $id = $request->user()->persona?->sucursal_id;
        abort_unless($id, 403, 'Tu usuario no tiene una sucursal asignada.');
        return (int) $id;
    }

    private function catalog(string $type): array
    {
        return match ($type) {
            'pacientes' => [PacienteClinico::class, 'Pacientes', 'pacientes', [
                'tipo_documento' => 'required|string|max:30', 'numero_documento' => 'nullable|string|max:30',
                'nombres' => 'required|string|max:120', 'apellidos' => 'required|string|max:120',
                'fecha_nacimiento' => 'nullable|date|before_or_equal:today', 'sexo' => 'nullable|string|max:20',
                'telefono' => 'nullable|string|max:40', 'email' => 'nullable|email|max:150', 'direccion' => 'nullable|string|max:255',
                'tutor_nombres' => 'nullable|string|max:180', 'tutor_documento' => 'nullable|string|max:30',
                'tutor_telefono' => 'nullable|string|max:40', 'tutor_email' => 'nullable|email|max:150', 'observaciones' => 'nullable|string|max:2000',
                'acepta_notificaciones' => 'nullable|boolean',
            ]],
            'profesionales' => [ProfesionalClinico::class, 'Profesionales', 'profesionales', [
                'tipo_documento' => 'required|string|max:30', 'numero_documento' => 'nullable|string|max:30',
                'nombres' => 'required|string|max:120', 'apellidos' => 'required|string|max:120',
                'especialidad' => 'required|string|max:150', 'registro_profesional' => 'nullable|string|max:60',
                'telefono' => 'nullable|string|max:40', 'email' => 'nullable|email|max:150', 'activo' => 'nullable|boolean',
            ]],
            default => [ServicioClinico::class, 'Servicios clínicos', 'servicios-clinicos', [
                'nombre' => 'required|string|max:150', 'descripcion' => 'nullable|string|max:1000',
                'duracion_minutos' => 'required|integer|min:5|max:480', 'precio' => 'required|numeric|min:0|max:999999.99', 'activo' => 'nullable|boolean',
            ]],
        };
    }

    public function index(Request $request, string $type)
    {
        [$model, $title] = $this->catalog($type);
        $query = $model::where('sucursal_id', $this->sucursal($request));
        $search = trim((string) $request->query('q'));
        if ($search !== '') {
            $query->where(function ($q) use ($search, $type) {
                foreach ($type === 'servicios-clinicos' ? ['nombre', 'descripcion'] : ['nombres', 'apellidos', 'numero_documento', 'telefono', 'email'] as $column) $q->orWhere($column, 'ilike', "%{$search}%");
            });
        }
        $perPage = (int) $request->query('perPage', 10);
        if (!in_array($perPage, [10, 25, 50, 100], true)) $perPage = 10;
        $rows = $query->latest('id')->paginate($perPage)->withQueryString();
        return view('clinica.index', compact('rows', 'title', 'type', 'search'));
    }

    public function create(Request $request, string $type)
    {
        [, $title] = $this->catalog($type);
        if ($type === 'pacientes') {
            $sucursal_id = $this->sucursal($request);
            $sucursal = Sucursal::findOrFail($sucursal_id);
            $persona = new Persona();
            $personamaestros = Personamaestro::get();
            $departamentos = Ubigeo::where('tipo', '2')->get();
            $provincias = Ubigeo::where('tipo', '3')->where('ubigeo_id', $sucursal->ubigeo->ubigeo->ubigeo_id)->get();
            $distritos = Ubigeo::where('tipo', '4')->where('ubigeo_id', $sucursal->ubigeo->ubigeo_id)->get();
            $perfiles = Perfile::where('id', '!=', 9)->get();
            $opciones = Menuopcione::where('estado', true)->get();
            $roles = Rol::get();
            $opcion = 'documento';
            $accion = 'CLINICA';
            $registroClinico = true;
            $row = null;

            return view('clinica.persona-form', compact(
                'persona', 'personamaestros', 'departamentos', 'provincias', 'distritos',
                'sucursal_id', 'sucursal', 'perfiles', 'opciones', 'roles', 'opcion',
                'accion', 'registroClinico', 'row', 'title', 'type'
            ));
        }
        return view('clinica.form', ['row' => null, 'title' => $title, 'type' => $type]);
    }

    public function store(Request $request, string $type)
    {
        [$model, $title, $route, $rules] = $this->catalog($type);
        if (in_array($type, ['pacientes', 'profesionales'], true)) {
            $rules['numero_documento'] = ['nullable', 'string', 'max:30', Rule::unique($type === 'pacientes' ? 'paciente_clinico' : 'profesional_clinico', 'numero_documento')
                ->where('sucursal_id', $this->sucursal($request))->where('tipo_documento', $request->input('tipo_documento'))];
        }
        $data = $request->validate($rules);
        if ($type === 'pacientes' && !empty($data['fecha_nacimiento']) && Carbon::parse($data['fecha_nacimiento'])->age < 18 && empty($data['tutor_nombres'])) {
            return back()->withErrors(['tutor_nombres' => 'Registra al padre, madre o tutor para pacientes menores de edad.'])->withInput();
        }
        if ($type === 'pacientes') {
            $data['acepta_notificaciones'] = $request->boolean('acepta_notificaciones');
            $data['acepta_notificaciones_at'] = $data['acepta_notificaciones'] ? now() : null;
        }
        if (in_array($type, ['profesionales', 'servicios-clinicos'], true)) $data['activo'] = $request->boolean('activo');
        $model::create($data + ['sucursal_id' => $this->sucursal($request)]);
        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => "$title registrado correctamente.", 'redirect_url' => route($route . '.index')]);
        }
        return redirect()->route($route . '.index')->with('success', "$title registrado correctamente.");
    }

    public function edit(Request $request, string $type, int $id)
    {
        [$model, $title] = $this->catalog($type);
        $row = $model::where('sucursal_id', $this->sucursal($request))->findOrFail($id);
        if ($type === 'pacientes' && $row->persona_id && ($persona = Persona::where('sucursal_id', $row->sucursal_id)->find($row->persona_id))) {
            $sucursal_id = $row->sucursal_id;
            $sucursal = Sucursal::findOrFail($sucursal_id);
            $personamaestros = Personamaestro::get();
            $departamentos = Ubigeo::where('tipo', '2')->get();
            $provincias = Ubigeo::where('tipo', '3')->where('ubigeo_id', $sucursal->ubigeo->ubigeo->ubigeo_id)->get();
            $distritos = Ubigeo::where('tipo', '4')->where('ubigeo_id', $sucursal->ubigeo->ubigeo_id)->get();
            $perfiles = Perfile::where('id', '!=', 9)->get();
            $opciones = Menuopcione::where('estado', true)->get();
            $roles = Rol::get();
            $opcion = 'documento';
            $accion = 'CLINICA';
            $registroClinico = true;
            $editing = true;

            return view('clinica.persona-form', compact(
                'persona', 'personamaestros', 'departamentos', 'provincias', 'distritos',
                'sucursal_id', 'sucursal', 'perfiles', 'opciones', 'roles', 'opcion',
                'accion', 'registroClinico', 'row', 'title', 'type', 'editing'
            ));
        }
        return view('clinica.form', compact('row', 'title', 'type'));
    }

    public function update(Request $request, string $type, int $id)
    {
        [$model, $title, $route, $rules] = $this->catalog($type);
        $row = $model::where('sucursal_id', $this->sucursal($request))->findOrFail($id);
        if (in_array($type, ['pacientes', 'profesionales'], true)) {
            $rules['numero_documento'] = ['nullable', 'string', 'max:30', Rule::unique($type === 'pacientes' ? 'paciente_clinico' : 'profesional_clinico', 'numero_documento')
                ->where('sucursal_id', $this->sucursal($request))->where('tipo_documento', $request->input('tipo_documento'))->ignore($id)];
        }
        $data = $request->validate($rules);
        if ($type === 'pacientes' && !empty($data['fecha_nacimiento']) && Carbon::parse($data['fecha_nacimiento'])->age < 18 && empty($data['tutor_nombres'])) {
            return back()->withErrors(['tutor_nombres' => 'Registra al padre, madre o tutor para pacientes menores de edad.'])->withInput();
        }
        if ($type === 'pacientes') {
            $authorized = $request->boolean('acepta_notificaciones');
            $data['acepta_notificaciones'] = $authorized;
            if ($authorized && !$row->acepta_notificaciones) $data['acepta_notificaciones_at'] = now();
            if (!$authorized) $data['acepta_notificaciones_at'] = null;
        }
        if (in_array($type, ['profesionales', 'servicios-clinicos'], true)) $data['activo'] = $request->boolean('activo');
        $row->update($data);
        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => "$title actualizado correctamente.", 'redirect_url' => route($route . '.index')]);
        }
        return redirect()->route($route . '.index')->with('success', "$title actualizado correctamente.");
    }

    public function destroy(Request $request, string $type, int $id)
    {
        [$model, $title, $route] = $this->catalog($type);
        $row = $model::where('sucursal_id', $this->sucursal($request))->findOrFail($id);
        if ($row->citas()->exists()) return back()->with('error', 'No se puede eliminar: tiene citas relacionadas.');
        $row->delete();
        return redirect()->route($route . '.index')->with('success', "$title eliminado.");
    }

    private function citaQuery(Request $request)
    {
        return CitaClinica::with(['paciente', 'profesional', 'servicio'])
            ->where('sucursal_id', $this->sucursal($request));
    }

    public function citas(Request $request)
    {
        $filters = $request->validate(['fecha' => 'nullable|date', 'profesional_id' => 'nullable|integer', 'servicio_id' => 'nullable|integer']);
        $date = $filters['fecha'] ?? now()->toDateString();
        $branch = $this->sucursal($request);
        $query = $this->citaQuery($request)->whereDate('inicio', $date);
        if (!empty($filters['profesional_id'])) $query->where('profesional_id', $filters['profesional_id']);
        if (!empty($filters['servicio_id'])) $query->where('servicio_id', $filters['servicio_id']);
        $rows = $query->orderBy('inicio')->paginate(30)->withQueryString();
        $pacientes = PacienteClinico::where('sucursal_id', $branch)->orderBy('nombres')->get();
        $profesionales = ProfesionalClinico::where('sucursal_id', $branch)->where('activo', true)->orderBy('nombres')->get();
        $servicios = ServicioClinico::where('sucursal_id', $branch)->where('activo', true)->orderBy('nombre')->get();
        return view('clinica.citas', compact('rows', 'pacientes', 'profesionales', 'servicios', 'date'));
    }

    public function reservar(Request $request)
    {
        $branch = $this->sucursal($request);
        $data = $request->validate([
            'paciente_id' => ['required', 'integer', Rule::exists('paciente_clinico', 'id')->where('sucursal_id', $branch)],
            'profesional_id' => ['required', 'integer', Rule::exists('profesional_clinico', 'id')->where('sucursal_id', $branch)->where('activo', true)],
            'servicio_id' => ['required', 'integer', Rule::exists('servicio_clinico', 'id')->where('sucursal_id', $branch)->where('activo', true)],
            'inicio' => 'required|date|after_or_equal:now', 'motivo' => 'nullable|string|max:1000', 'notas' => 'nullable|string|max:2000',
        ]);
        $service = ServicioClinico::where('sucursal_id', $branch)->findOrFail($data['servicio_id']);
        $start = Carbon::parse($data['inicio']);
        $end = $start->copy()->addMinutes((int) $service->duracion_minutos);
        DB::transaction(function () use ($data, $branch, $start, $end) {
            $professional = ProfesionalClinico::where('sucursal_id', $branch)->whereKey($data['profesional_id'])->lockForUpdate()->firstOrFail();
            PacienteClinico::where('sucursal_id', $branch)->whereKey($data['paciente_id'])->lockForUpdate()->firstOrFail();
            if (!$this->dentroDeHorario($branch, $professional->id, $start, $end)) {
                throw \Illuminate\Validation\ValidationException::withMessages(['inicio' => 'El profesional no atiende en ese horario. Revisa sus horarios configurados.']);
            }
            if ($this->hayConflicto($branch, $data['profesional_id'], $data['paciente_id'], $start, $end)) {
                throw \Illuminate\Validation\ValidationException::withMessages(['inicio' => 'El profesional o el paciente ya tienen una cita en ese horario.']);
            }
            CitaClinica::create($data + ['sucursal_id' => $branch, 'inicio' => $start, 'fin' => $end, 'estado' => 'RESERVADA']);
        });
        return redirect()->route('citas.index', ['fecha' => $start->toDateString()])->with('success', 'Cita reservada.');
    }

    public function disponibilidad(Request $request)
    {
        $branch = $this->sucursal($request);
        $data = $request->validate([
            'profesional_id' => ['required', 'integer', Rule::exists('profesional_clinico', 'id')->where('sucursal_id', $branch)->where('activo', true)],
            'servicio_id' => ['required', 'integer', Rule::exists('servicio_clinico', 'id')->where('sucursal_id', $branch)->where('activo', true)],
            'fecha' => 'required|date|after_or_equal:today',
            'paciente_id' => ['nullable', 'integer', Rule::exists('paciente_clinico', 'id')->where('sucursal_id', $branch)],
        ]);

        $service = ServicioClinico::where('sucursal_id', $branch)->findOrFail($data['servicio_id']);
        $duration = (int) $service->duracion_minutos;
        $date = Carbon::parse($data['fecha'])->startOfDay();
        $windows = HorarioProfesionalClinico::where('sucursal_id', $branch)
            ->where('profesional_id', $data['profesional_id'])->where('dia_semana', $date->dayOfWeekIso)
            ->where('activo', true)->orderBy('hora_inicio')->get();

        $slots = [];
        foreach ($windows as $window) {
            $start = $date->copy()->setTimeFromTimeString($window->hora_inicio);
            $windowEnd = $date->copy()->setTimeFromTimeString($window->hora_fin);
            while ($start->copy()->addMinutes($duration)->lte($windowEnd)) {
                $end = $start->copy()->addMinutes($duration);
                $isFuture = !$start->isPast();
                $conflict = CitaClinica::where('sucursal_id', $branch)
                    ->whereNotIn('estado', ['CANCELADA', 'ANULADA'])
                    ->where('inicio', '<', $end)->where('fin', '>', $start)
                    ->where(function ($query) use ($data) {
                        $query->where('profesional_id', $data['profesional_id']);
                        if (!empty($data['paciente_id'])) $query->orWhere('paciente_id', $data['paciente_id']);
                    })->exists();
                if ($isFuture && !$conflict) $slots[] = ['inicio' => $start->toIso8601String(), 'fin' => $end->toIso8601String(), 'hora' => $start->format('H:i')];
                $start->addMinutes($duration);
            }
        }

        return response()->json(['fecha' => $date->toDateString(), 'duracion_minutos' => $duration, 'horarios' => $slots]);
    }

    public function estado(Request $request, int $id)
    {
        $data = $request->validate(['estado' => 'required|in:CONFIRMADA,ATENDIDA,CANCELADA,NO_ASISTIO,POSTERGADA', 'notas' => 'nullable|string|max:2000', 'nuevo_inicio' => 'nullable|date|after_or_equal:now']);
        return DB::transaction(function () use ($request, $id, $data) {
            $cita = $this->citaQuery($request)->lockForUpdate()->findOrFail($id);
            $allowed = [
                'RESERVADA' => ['CONFIRMADA', 'CANCELADA', 'POSTERGADA', 'NO_ASISTIO', 'ATENDIDA'],
                'CONFIRMADA' => ['CANCELADA', 'POSTERGADA', 'NO_ASISTIO', 'ATENDIDA'],
                'POSTERGADA' => ['CONFIRMADA', 'CANCELADA', 'POSTERGADA'],
            ];
            abort_unless(in_array($data['estado'], $allowed[$cita->estado] ?? [], true), 422, 'Transición de estado no permitida.');
            $changes = ['estado' => $data['estado'], 'notas' => $data['notas'] ?? $cita->notas];
            if ($data['estado'] === 'POSTERGADA') {
                if (empty($data['nuevo_inicio'])) return back()->withErrors(['nuevo_inicio' => 'Indica la nueva fecha y hora para postergar.']);
                $newStart = Carbon::parse($data['nuevo_inicio']);
                $newEnd = $newStart->copy()->addMinutes($cita->servicio->duracion_minutos);
                $branch = $this->sucursal($request);
                $professional = ProfesionalClinico::where('sucursal_id', $branch)->whereKey($cita->profesional_id)->lockForUpdate()->firstOrFail();
                PacienteClinico::where('sucursal_id', $branch)->whereKey($cita->paciente_id)->lockForUpdate()->firstOrFail();
                if (!$this->dentroDeHorario($branch, $professional->id, $newStart, $newEnd)) {
                    return back()->withErrors(['nuevo_inicio' => 'El profesional no atiende en ese horario.']);
                }
                if ($this->hayConflicto($branch, $cita->profesional_id, $cita->paciente_id, $newStart, $newEnd, $cita->id)) {
                    return back()->withErrors(['nuevo_inicio' => 'El profesional o el paciente ya tienen otra cita en ese horario.']);
                }
                $changes['inicio'] = $newStart;
                $changes['fin'] = $newEnd;
            }
            $cita->update($changes);
            return back()->with('success', 'Estado de la cita actualizado.');
        });
    }

    private function hayConflicto(int $branch, int $professionalId, int $patientId, Carbon $start, Carbon $end, ?int $ignoreId = null): bool
    {
        return CitaClinica::where('sucursal_id', $branch)->whereNotIn('estado', ['CANCELADA', 'ANULADA'])
            ->when($ignoreId, fn ($q) => $q->where('id', '<>', $ignoreId))
            ->where('inicio', '<', $end)->where('fin', '>', $start)
            ->where(function ($q) use ($professionalId, $patientId) {
                $q->where('profesional_id', $professionalId)->orWhere('paciente_id', $patientId);
            })->exists();
    }

    private function dentroDeHorario(int $branch, int $professionalId, Carbon $start, Carbon $end): bool
    {
        return HorarioProfesionalClinico::where('sucursal_id', $branch)->where('profesional_id', $professionalId)
            ->where('dia_semana', $start->dayOfWeekIso)->where('activo', true)
            ->where('hora_inicio', '<=', $start->format('H:i:s'))
            ->where('hora_fin', '>=', $end->format('H:i:s'))->exists();
    }

    public function horarios(Request $request)
    {
        $branch = $this->sucursal($request);
        $profesionales = ProfesionalClinico::where('sucursal_id', $branch)->where('activo', true)->orderBy('nombres')->get();
        $query = HorarioProfesionalClinico::with('profesional')->where('sucursal_id', $branch);
        if ($request->filled('profesional_id')) $query->where('profesional_id', $request->integer('profesional_id'));
        $rows = $query->orderBy('profesional_id')->orderBy('dia_semana')->orderBy('hora_inicio')->get();
        return view('clinica.horarios', compact('profesionales', 'rows'));
    }

    public function guardarHorario(Request $request)
    {
        $branch = $this->sucursal($request);
        $data = $request->validate([
            'profesional_id' => ['required', 'integer', Rule::exists('profesional_clinico', 'id')->where('sucursal_id', $branch)->where('activo', true)],
            'dia_semana' => 'required|integer|between:1,7', 'hora_inicio' => 'required|date_format:H:i',
            'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
        ]);
        DB::transaction(function () use ($data, $branch) {
            ProfesionalClinico::where('sucursal_id', $branch)->whereKey($data['profesional_id'])->lockForUpdate()->firstOrFail();
            $overlap = HorarioProfesionalClinico::where('sucursal_id', $branch)->where('profesional_id', $data['profesional_id'])
                ->where('dia_semana', $data['dia_semana'])->where('activo', true)
                ->where('hora_inicio', '<', $data['hora_fin'])->where('hora_fin', '>', $data['hora_inicio'])->exists();
            if ($overlap) throw \Illuminate\Validation\ValidationException::withMessages(['hora_inicio' => 'Ese horario se cruza con otro turno del profesional.']);
            HorarioProfesionalClinico::create($data + ['sucursal_id' => $branch, 'activo' => true]);
        });
        return back()->with('success', 'Horario registrado.');
    }

    public function eliminarHorario(Request $request, int $id)
    {
        HorarioProfesionalClinico::where('sucursal_id', $this->sucursal($request))->findOrFail($id)->delete();
        return back()->with('success', 'Horario eliminado.');
    }

    public function calendario(Request $request)
    {
        return view('clinica.calendario');
    }

    public function eventosCalendario(Request $request)
    {
        $range = $request->validate(['start' => 'required|date', 'end' => 'required|date|after:start']);
        $from = Carbon::parse($range['start']);
        $to = Carbon::parse($range['end']);
        $events = $this->citaQuery($request)->where('inicio', '<', $to)->where('fin', '>', $from)->get()->map(fn ($c) => [
            'id' => $c->id, 'title' => $c->paciente->nombres.' '.$c->paciente->apellidos.' · '.$c->servicio->nombre,
            'start' => $c->inicio->toIso8601String(), 'end' => $c->fin->toIso8601String(), 'extendedProps' => ['estado' => $c->estado, 'profesional' => $c->profesional->nombres.' '.$c->profesional->apellidos],
        ]);
        return response()->json($events);
    }

    public function reporte(Request $request)
    {
        $branch = $this->sucursal($request);
        $filters = $request->validate([
            'desde' => 'nullable|date',
            'hasta' => 'nullable|date|after_or_equal:desde',
        ]);
        $from = $filters['desde'] ?? now()->startOfMonth()->toDateString();
        $to = $filters['hasta'] ?? now()->toDateString();
        $query = $this->citaQuery($request)->whereBetween('inicio', [$from.' 00:00:00', $to.' 23:59:59']);
        $total = (clone $query)->count();
        $atendidas = (clone $query)->where('estado', 'ATENDIDA')->count();
        $elegiblesAsistencia = (clone $query)->whereNotIn('estado', ['CANCELADA', 'ANULADA'])->count();
        $tasaAsistencia = $elegiblesAsistencia > 0 ? round($atendidas * 100 / $elegiblesAsistencia, 1) : 0;
        $estados = (clone $query)->select('estado', DB::raw('count(*) as total'))->groupBy('estado')->pluck('total', 'estado');
        $porProfesional = (clone $query)->whereNotIn('estado', ['CANCELADA', 'ANULADA'])->select(
            'profesional_id',
            DB::raw('count(*) as total'),
            DB::raw("sum(case when estado = 'ATENDIDA' then 1 else 0 end) as atendidas")
        )->groupBy('profesional_id')->with('profesional')->get();
        $porServicio = (clone $query)->whereNotIn('estado', ['CANCELADA', 'ANULADA'])
            ->select('servicio_id', DB::raw('count(*) as total'))->groupBy('servicio_id')->with('servicio')->get();
        $proximas = $this->citaQuery($request)
            ->where('inicio', '>=', now())
            ->where('inicio', '<', now()->addDays(8)->startOfDay())
            ->whereNotIn('estado', ['CANCELADA', 'ANULADA'])
            ->orderBy('inicio')->limit(100)->get();
        $pacientes = PacienteClinico::where('sucursal_id', $branch)->count();
        return view('clinica.reporte', compact('from', 'to', 'total', 'atendidas', 'elegiblesAsistencia', 'tasaAsistencia', 'estados', 'porProfesional', 'porServicio', 'proximas', 'pacientes'));
    }

    public function avisos(Request $request)
    {
        $rows = NotificacionClinica::with(['paciente', 'cita'])
            ->where('sucursal_id', $this->sucursal($request))
            ->latest('fecha_evento')->latest('id')->paginate(30);
        return view('clinica.avisos', compact('rows'));
    }

    public function resolverAviso(Request $request, int $id)
    {
        $data = $request->validate(['resolucion' => 'required|in:ENTREGADO,NO_ENVIADO']);
        $branch = $this->sucursal($request);
        $notice = DB::transaction(function () use ($branch, $id, $data, $request) {
            $notice = NotificacionClinica::where('sucursal_id', $branch)->lockForUpdate()->findOrFail($id);
            $envioEstancado = $notice->estado === 'ENVIANDO' && $notice->updated_at?->lt(now()->subMinutes(2));
            abort_unless($notice->estado === 'INCIERTO' || $envioEstancado, 409, 'Solo se pueden resolver avisos inciertos o envíos detenidos.');

            $respuesta = $notice->respuesta ?? [];
            $respuesta['resolucion_manual'] = [
                'estado' => $data['resolucion'], 'usuario_id' => $request->user()->id,
                'usuario' => $request->user()->name, 'fecha' => now()->toIso8601String(),
            ];
            $notice->update([
                'estado' => $data['resolucion'] === 'ENTREGADO' ? 'ENVIADO' : 'FALLIDO',
                'respuesta' => $respuesta,
                'error' => $data['resolucion'] === 'ENTREGADO' ? null : 'Operador confirmó que WhatsApp no entregó el mensaje; se permite un reintento.',
                'enviado_at' => $data['resolucion'] === 'ENTREGADO' ? ($notice->enviado_at ?? now()) : null,
            ]);
            return $notice->fresh();
        });

        return back()->with('success', $notice->estado === 'ENVIADO'
            ? 'Aviso marcado como entregado según la verificación del proveedor.'
            : 'Aviso marcado como no enviado; ahora se permite reintentar.');
    }

    public function reenviarAviso(Request $request, int $id, ClinicalReminderService $service)
    {
        abort_unless((bool) config('services.meta_whatsapp.clinical_automatic'), 403, 'El envío clínico no está habilitado.');
        $notice = NotificacionClinica::where('sucursal_id', $this->sucursal($request))->findOrFail($id);
        abort_unless($notice->estado === 'FALLIDO', 409, 'Solo se pueden reintentar avisos rechazados de forma explícita.');
        abort_unless($notice->fecha_evento?->isToday(), 422, 'Solo se pueden reintentar avisos del día de hoy.');
        $candidate = collect($service->candidates(Carbon::parse($notice->fecha_evento)))
            ->firstWhere('key', $notice->clave);
        abort_unless($candidate, 422, 'El evento ya no está pendiente o no tiene un contacto disponible.');
        $service->send($candidate, true);
        return back()->with('success', 'Reintento procesado. Revisa el estado actualizado del aviso.');
    }
}
