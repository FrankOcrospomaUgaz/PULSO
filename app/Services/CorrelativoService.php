<?php

namespace App\Services;

use App\Models\Movimiento;
use Illuminate\Support\Facades\DB;

class CorrelativoService
{
    public const SIN_SERIE = '__SIN_SERIE__';

    public static function nextNumeroVenta(int $sucursalId, int $tipoDocumentoId, ?string $serieKey = null): string
    {
        $serieKey = self::normalizeSerieKey($serieKey);

        self::lockCorrelativo('venta', $sucursalId, $tipoDocumentoId, $serieKey);

        $ultimoNumero = self::getUltimoNumero($sucursalId, $tipoDocumentoId, $serieKey);
        $next = $ultimoNumero + 1;

        return str_pad((string) $next, 8, '0', STR_PAD_LEFT);
    }

    public static function peekNumeroVenta(int $sucursalId, int $tipoDocumentoId, ?string $serieKey = null): string
    {
        $serieKey = self::normalizeSerieKey($serieKey);

        $ultimoNumero = self::getUltimoNumero($sucursalId, $tipoDocumentoId, $serieKey);
        return str_pad((string) ($ultimoNumero + 1), 8, '0', STR_PAD_LEFT);
    }

    public static function nextNumeroCompra(int $sucursalId, int $tipoDocumentoId, ?string $serieKey = null): string
    {
        $serieKey = self::normalizeSerieKey($serieKey);

        self::lockCorrelativo('compra', $sucursalId, $tipoDocumentoId, $serieKey);

        $ultimoNumero = self::getUltimoNumeroCompra($sucursalId, $tipoDocumentoId, $serieKey);

        return str_pad((string) ($ultimoNumero + 1), 8, '0', STR_PAD_LEFT);
    }

    public static function peekNumeroCompra(int $sucursalId, int $tipoDocumentoId, ?string $serieKey = null): string
    {
        $serieKey = self::normalizeSerieKey($serieKey);
        $ultimoNumero = self::getUltimoNumeroCompra($sucursalId, $tipoDocumentoId, $serieKey);

        return str_pad((string) ($ultimoNumero + 1), 8, '0', STR_PAD_LEFT);
    }

    private static function normalizeSerieKey(?string $serieKey): string
    {
        $serieKey = trim((string) $serieKey);
        return $serieKey === '' ? self::SIN_SERIE : $serieKey;
    }

    private static function lockCorrelativo(string $tipo, int $sucursalId, int $tipoDocumentoId, string $serieKey): void
    {
        $key = implode(':', [$tipo, $sucursalId, $tipoDocumentoId, $serieKey]);

        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::select('select pg_advisory_xact_lock(hashtext(?))', [$key]);
            return;
        }

        DB::table('sucursal')
            ->where('id', $sucursalId)
            ->lockForUpdate()
            ->first();
    }

    private static function getUltimoNumero(int $sucursalId, int $tipoDocumentoId, string $serieKey): int
    {
        $query = Movimiento::where('sucursal_id', $sucursalId);

        if ($serieKey !== self::SIN_SERIE) {
            $query->whereHas('movventa', function ($q) use ($serieKey) {
                $q->where('serie', $serieKey);
            });
        } else {
            $query->where('tipodocumento_id', $tipoDocumentoId);
        }

        $ultimoMov = $query->orderByDesc('numero')->select('numero')->first();

        return $ultimoMov ? (int) $ultimoMov->numero : 0;
    }

    private static function getUltimoNumeroCompra(int $sucursalId, int $tipoDocumentoId, string $serieKey): int
    {
        $query = Movimiento::where('sucursal_id', $sucursalId)
            ->where('tipomovimiento_id', 1);

        if ($serieKey !== self::SIN_SERIE) {
            $query->whereHas('movcompra', function ($q) use ($serieKey) {
                $q->where('serie', $serieKey);
            });
        } else {
            $query->where('tipodocumento_id', $tipoDocumentoId);
        }

        $ultimoMov = $query->orderByDesc('numero')->select('numero')->first();

        return $ultimoMov ? (int) $ultimoMov->numero : 0;
    }
}
