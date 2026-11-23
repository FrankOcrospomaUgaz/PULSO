<?php

namespace App\Console\Commands;

use App\Events\CheckoutReminder;
use App\Models\MovhabitacionHabitacion;
use App\Models\RelParametrosucursal;
use Illuminate\Support\Carbon;
use Illuminate\Console\Command;

class SendCheckoutReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'habitaciones:checkout-reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notifica habitaciones con checkout segun tolerancia por sucursal';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $ahora = Carbon::now();

        $toleranciaDefault = 10;
        $tolerancias = RelParametrosucursal::whereHas('parametro', function ($query) {
            $query->where('descripcion', 'Tolerancia checkout');
        })->get(['sucursal_id', 'valor']);

        $toleranciaPorSucursal = $tolerancias->mapWithKeys(function ($rel) {
            $minutos = is_numeric($rel->valor) ? (int) $rel->valor : null;
            $minutos = $minutos !== null ? max(0, $minutos) : null;
            return [(int) $rel->sucursal_id => $minutos];
        });

        $maxTolerancia = max($toleranciaDefault, (int) ($toleranciaPorSucursal->max() ?? 0));
        $limiteGlobal = $ahora->copy()->addMinutes($maxTolerancia);
        $inicioGlobal = $ahora->copy()->subMinutes($maxTolerancia);

        $detalles = MovhabitacionHabitacion::with(['habitacion', 'movhabitacion'])
            ->whereIn('situacion', ['A', 'P'])
            ->where(function ($query) {
                $query->whereNull('notificado_checkout_10')
                    ->orWhere('notificado_checkout_10', false);
            })
            ->whereHas('movhabitacion', function ($query) use ($inicioGlobal, $limiteGlobal) {
                $query->whereNotNull('fechasalida')
                    ->whereBetween('fechasalida', [$inicioGlobal, $limiteGlobal])
                    ->whereNotIn('situacion', ['C', 'D']);
            })
            ->get();

        $enviadas = 0;
        foreach ($detalles as $detalle) {
            $sucursalId = (int) ($detalle->movhabitacion?->sucursal_id
                ?? $detalle->habitacion?->sucursal_id
                ?? 0);
            if (!$sucursalId) {
                continue;
            }

            $tolerancia = $toleranciaPorSucursal[$sucursalId] ?? $toleranciaDefault;
            $tolerancia = is_numeric($tolerancia) ? (int) $tolerancia : $toleranciaDefault;
            $tolerancia = max(0, $tolerancia);

            $checkout = $detalle->movhabitacion?->fechasalida ?? $detalle->fechacheckout;
            if (!$checkout) {
                continue;
            }

            $inicio = $ahora->copy()->subMinutes($tolerancia);
            $limite = $ahora->copy()->addMinutes($tolerancia);
            $fechaCheckout = Carbon::parse($checkout);
            if (!$fechaCheckout->between($inicio, $limite)) {
                continue;
            }

            event(new CheckoutReminder($detalle));
            $detalle->notificado_checkout_10 = true;
            $detalle->save();
            $enviadas++;
        }

        $this->info("Notificaciones enviadas: {$enviadas}");

        return self::SUCCESS;
    }
}
