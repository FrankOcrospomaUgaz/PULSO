<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $descripcion = 'Modificar precios en checkin';

        $parametroExistente = DB::table('parametros')
            ->where('descripcion', $descripcion)
            ->whereNull('deleted_at')
            ->first();

        if ($parametroExistente) {
            $this->asignarParametroATodasLasSucursales((int) $parametroExistente->id, 'No');

            return;
        }

        $categoria = DB::table('categoriaparametros')
            ->whereRaw('LOWER(descripcion) = ?', ['alojamientos'])
            ->whereNull('deleted_at')
            ->first();

        if (!$categoria) {
            return;
        }

        $parametroReferencia = DB::table('parametros')
            ->where('descripcion', 'Suite')
            ->whereNull('deleted_at')
            ->first();

        $tablaId = $parametroReferencia->tabla_id ?? DB::table('parametros')
            ->where('categoriaparametro_id', $categoria->id)
            ->whereNull('deleted_at')
            ->value('tabla_id');

        if (!$tablaId) {
            $tablaId = DB::table('tablas')->whereNull('deleted_at')->value('id') ?? 1;
        }

        $now = now();

        $parametroId = DB::table('parametros')->insertGetId([
            'descripcion' => $descripcion,
            'valor' => 'No',
            'tipo' => 'SUCURSAL',
            'obligatorio' => 'N',
            'estado' => true,
            'categoriaparametro_id' => $categoria->id,
            'tabla_id' => $tablaId,
            'created_at' => $now,
            'updated_at' => $now,
            'deleted_at' => null,
        ]);

        $this->asignarParametroATodasLasSucursales($parametroId, 'No');
    }

    private function asignarParametroATodasLasSucursales(int $parametroId, string $valorPorDefecto): void
    {
        $now = now();

        $sucursalIds = DB::table('sucursal')
            ->whereNull('deleted_at')
            ->pluck('id');

        foreach ($sucursalIds as $sucursalId) {
            $yaExiste = DB::table('rel_parametrosucursal')
                ->where('sucursal_id', $sucursalId)
                ->where('parametro_id', $parametroId)
                ->whereNull('deleted_at')
                ->exists();

            if ($yaExiste) {
                continue;
            }

            DB::table('rel_parametrosucursal')->insert([
                'valor' => $valorPorDefecto,
                'parametro_id' => $parametroId,
                'sucursal_id' => $sucursalId,
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $descripcion = 'Modificar precios en checkin';

        $parametro = DB::table('parametros')
            ->where('descripcion', $descripcion)
            ->whereNull('deleted_at')
            ->first();

        if (!$parametro) {
            return;
        }

        $now = now();

        DB::table('rel_parametrosucursal')
            ->where('parametro_id', $parametro->id)
            ->whereNull('deleted_at')
            ->update(['deleted_at' => $now]);

        DB::table('parametros')
            ->where('id', $parametro->id)
            ->update(['deleted_at' => $now]);
    }
};
