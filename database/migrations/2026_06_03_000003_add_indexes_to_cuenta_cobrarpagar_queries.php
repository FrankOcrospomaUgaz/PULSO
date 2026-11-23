<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cuenta_cobrarpagar', function (Blueprint $table) {
            $table->index(['sucursal_id', 'tipo', 'deleted_at', 'id'], 'idx_ccp_sucursal_tipo_deleted_id');
            $table->index(['sucursal_id', 'tipo', 'estado', 'fechavencimiento'], 'idx_ccp_sucursal_tipo_estado_venc');
            $table->index(['sucursal_id', 'tipo', 'fecha'], 'idx_ccp_sucursal_tipo_fecha');
        });

        Schema::table('movcaja', function (Blueprint $table) {
            $table->index(['id', 'total'], 'idx_movcaja_id_total');
            $table->index(['caja_id'], 'idx_movcaja_caja_id');
            $table->index(['conceptopago_id'], 'idx_movcaja_conceptopago_id');
            $table->index(['movimiento_id'], 'idx_movcaja_movimiento_id');
        });
    }

    public function down(): void
    {
        Schema::table('movcaja', function (Blueprint $table) {
            $table->dropIndex('idx_movcaja_id_total');
            $table->dropIndex('idx_movcaja_caja_id');
            $table->dropIndex('idx_movcaja_conceptopago_id');
            $table->dropIndex('idx_movcaja_movimiento_id');
        });

        Schema::table('cuenta_cobrarpagar', function (Blueprint $table) {
            $table->dropIndex('idx_ccp_sucursal_tipo_deleted_id');
            $table->dropIndex('idx_ccp_sucursal_tipo_estado_venc');
            $table->dropIndex('idx_ccp_sucursal_tipo_fecha');
        });
    }
};
