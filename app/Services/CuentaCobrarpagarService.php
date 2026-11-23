<?php

namespace App\Services;

use App\Models\CuentaCobrarpagar;
use App\Models\DetCuentaCobrarpagar;
use App\Models\DetMovcaja;
use App\Models\Movcaja;
use Illuminate\Support\Facades\Schema;

class CuentaCobrarpagarService
{
    private static ?bool $tablasDisponibles = null;

    public static function sincronizarDesdeMovcajaId(?int $movcajaId): void
    {
        if (!$movcajaId || !self::tablasDisponibles()) {
            return;
        }

        $movcaja = Movcaja::with(['conceptopago', 'movimiento.tipodocumento', 'detMovcaja'])
            ->find($movcajaId);

        if (!$movcaja || !$movcaja->movimiento) {
            return;
        }

        $deudas = $movcaja->detMovcaja
            ->filter(fn($detalle) => strcasecmp((string) $detalle->tipo, 'Deuda') === 0);

        if ($deudas->isEmpty()) {
            CuentaCobrarpagar::withTrashed()->where('movcaja_id', $movcaja->id)->forceDelete();
            return;
        }

        $tipo = self::tipoCuenta($movcaja);
        $pagado = self::totalPagado($movcaja);
        $total = (float) $movcaja->total;
        $estado = $pagado <= 0 ? 'NUEVO' : ($pagado >= $total ? 'PAGADO' : 'PAGANDO');
        $fechaPago = $pagado > 0 ? self::ultimaFechaPago($movcaja) : null;
        $primerVencimiento = $deudas
            ->filter(fn($detalle) => !empty($detalle->fechavencimiento))
            ->sortBy('fechavencimiento')
            ->first();

        $cuenta = CuentaCobrarpagar::withTrashed()->updateOrCreate(
            [
                'movcaja_id' => $movcaja->id,
                'tipo' => $tipo,
            ],
            [
                'numero' => str_pad((string) $movcaja->id, 8, '0', STR_PAD_LEFT),
                'estado' => $estado,
                'fecha' => $movcaja->movimiento->fecha ?? $movcaja->created_at ?? now(),
                'fechavencimiento' => $primerVencimiento?->fechavencimiento ?? $movcaja->created_at ?? now(),
                'fechapago' => $fechaPago,
                'moneda' => $movcaja->moneda,
                'tipocambio' => $movcaja->tipocambio,
                'totalpagado' => $pagado,
                'situacion' => 'A',
                'sucursal_id' => $movcaja->sucursal_id,
            ]
        );

        if ($cuenta->trashed()) {
            $cuenta->restore();
        }

        self::sincronizarDetallesPagos($cuenta, $movcaja);
    }

    private static function tablasDisponibles(): bool
    {
        if (self::$tablasDisponibles === null) {
            self::$tablasDisponibles = Schema::hasTable('cuenta_cobrarpagar')
                && Schema::hasTable('det_cuenta_cobrarpagar');
        }

        return self::$tablasDisponibles;
    }

    private static function tipoCuenta(Movcaja $movcaja): string
    {
        $tipoConcepto = $movcaja->conceptopago?->getRawOriginal('tipo');

        if ((int) $movcaja->movimiento->tipodocumento_id === 8 || $tipoConcepto === 'E') {
            return 'PAGAR';
        }

        return 'COBRAR';
    }

    private static function totalPagado(Movcaja $movcaja): float
    {
        $pagadoInicial = $movcaja->detMovcaja
            ->filter(fn($detalle) => strcasecmp((string) $detalle->tipo, 'Pagado') === 0)
            ->sum('monto');

        $pagosPosteriores = DetCuentaCobrarpagar::whereHas('cuenta', function ($query) use ($movcaja) {
            $query->where('movcaja_id', $movcaja->id);
        })
            ->whereHas('movcaja.movimiento', fn($query) => $query->where('situacion', 'A'))
            ->with('movcaja')
            ->get()
            ->sum(fn($detalle) => (float) ($detalle->movcaja->total ?? 0));

        return round((float) $pagadoInicial + (float) $pagosPosteriores, 2);
    }

    private static function ultimaFechaPago(Movcaja $movcaja)
    {
        $fechaInicial = $movcaja->detMovcaja
            ->filter(fn($detalle) => strcasecmp((string) $detalle->tipo, 'Pagado') === 0)
            ->max('fechapago');

        $fechaPosterior = DetCuentaCobrarpagar::whereHas('cuenta', function ($query) use ($movcaja) {
            $query->where('movcaja_id', $movcaja->id);
        })
            ->whereHas('movcaja.movimiento', fn($query) => $query->where('situacion', 'A'))
            ->with('movcaja')
            ->get()
            ->max(fn($detalle) => $detalle->movcaja->created_at);

        return $fechaPosterior ?? $fechaInicial ?? now();
    }

    private static function sincronizarDetallesPagos(CuentaCobrarpagar $cuenta, Movcaja $movcaja): void
    {
        DetCuentaCobrarpagar::where('cuenta_cobrarpagar_id', $cuenta->id)
            ->where('movcaja_id', $movcaja->id)
            ->delete();
    }
}
