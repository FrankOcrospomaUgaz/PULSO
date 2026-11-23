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
        DB::unprepared(<<<'SQL'
DO $$
DECLARE
    rec RECORD;
    new_mov_id BIGINT;
BEGIN
    FOR rec IN
        SELECT m.*, mv.id AS movventa_id, mh.id AS movhabitacion_id
        FROM movimiento m
        INNER JOIN movventa mv ON mv.movimiento_id = m.id
        INNER JOIN movhabitacion mh ON mh.movimiento_id = m.id
        WHERE m.deleted_at IS NULL
    LOOP
        -- 1) Crear nuevo movimiento exclusivo para la habitacion
        INSERT INTO movimiento (
            numero, fecha, usuario_id, usuario, persona_id, persona,
            responsable_id, responsable, comentario, situacion,
            tipomovimiento_id, tipodocumento_id, sucursal_id,
            movimiento_id, created_at, updated_at, deleted_at
        )
        VALUES (
            rec.numero, rec.fecha, rec.usuario_id, rec.usuario, rec.persona_id, rec.persona,
            rec.responsable_id, rec.responsable, rec.comentario, rec.situacion,
            8, 11,
            rec.sucursal_id,
            rec.id,
            NOW(), NOW(), NULL
        )
        RETURNING id INTO new_mov_id;

        -- 2) Actualizar movhabitacion con el nuevo movimiento
        UPDATE movhabitacion
        SET movimiento_id = new_mov_id
        WHERE id = rec.movhabitacion_id;
    END LOOP;
END $$;
DO $$
DECLARE
    rec RECORD;
    new_movreserva_mov_id BIGINT;
BEGIN
    FOR rec IN
        SELECT
            mr.id               AS movreserva_id,
            mr.movimiento_id    AS old_movimiento_id,
            mr.sucursal_id,
            mh.id               AS movhabitacion_id,
            mh.movimiento_id    AS movhabitacion_movimiento_id
        FROM movreserva mr
        LEFT JOIN movhabitacion mh
            ON mh.movreserva_id = mr.id
           AND mh.deleted_at IS NULL
        WHERE mr.deleted_at IS NULL
    LOOP

        -- =====================================================
        -- 1️⃣ CREAR MOVIMIENTO PROPIO PARA MOVRESERVA
        --    (movimiento_id = NULL)
        -- =====================================================
        INSERT INTO movimiento (
            numero,
            fecha,
            usuario_id,
            usuario,
            persona_id,
            persona,
            responsable_id,
            responsable,
            comentario,
            situacion,
            tipomovimiento_id,
            tipodocumento_id,
            sucursal_id,
            movimiento_id,
            created_at,
            updated_at,
            deleted_at
        )
        SELECT
            m.numero,
            m.fecha,
            m.usuario_id,
            m.usuario,
            m.persona_id,
            m.persona,
            m.responsable_id,
            m.responsable,
            m.comentario,
            m.situacion,
            10,     -- RESERVA
            12,     -- DOC RESERVA
            m.sucursal_id,
            NULL,   -- 👈 CLAVE: SIN PADRE
            NOW(),
            NOW(),
            NULL
        FROM movimiento m
        WHERE m.id = rec.old_movimiento_id
        RETURNING id INTO new_movreserva_mov_id;

        -- =====================================================
        -- 2️⃣ ACTUALIZAR MOVRESERVA → SU NUEVO MOVIMIENTO
        -- =====================================================
        UPDATE movreserva
        SET movimiento_id = new_movreserva_mov_id
        WHERE id = rec.movreserva_id;

        -- =====================================================
        -- 3️⃣ SI EXISTE MOVHABITACION, APUNTARLO AL MOVRESERVA
        -- =====================================================
        IF rec.movhabitacion_movimiento_id IS NOT NULL THEN
            UPDATE movimiento
            SET movimiento_id = new_movreserva_mov_id
            WHERE id = rec.movhabitacion_movimiento_id;
        END IF;

    END LOOP;
END $$;


DO $$
DECLARE
    rec RECORD;
    new_mov_id BIGINT;
BEGIN
    FOR rec IN
        SELECT m.*, mv.id AS movventa_id, mc.id AS movcaja_id
        FROM movimiento m
        INNER JOIN movventa mv ON mv.movimiento_id = m.id
        INNER JOIN movcaja mc ON mc.movimiento_id = m.id
        WHERE m.deleted_at IS NULL
    LOOP
        -- 1) Crear nuevo movimiento para movcaja
        INSERT INTO movimiento (
            numero, fecha, usuario_id, usuario, persona_id, persona,
            responsable_id, responsable, comentario, situacion,
            tipomovimiento_id, tipodocumento_id, sucursal_id,
            movimiento_id, created_at, updated_at, deleted_at
        )
        VALUES (
            rec.numero, rec.fecha, rec.usuario_id, rec.usuario, rec.persona_id, rec.persona,
            rec.responsable_id, rec.responsable, rec.comentario, rec.situacion,
            4, 7, rec.sucursal_id,
            rec.id,
            NOW(), NOW(), NULL
        )
        RETURNING id INTO new_mov_id;

        -- 2) Actualizar movcaja con el nuevo movimiento
        UPDATE movcaja
        SET movimiento_id = new_mov_id
        WHERE id = rec.movcaja_id;
    END LOOP;
END $$;
DO $$
DECLARE
    grp RECORD;     -- agrupación por sucursal / caja / concepto
    r   RECORD;     -- cada movimiento
    contador INTEGER;
BEGIN
    -- =====================================================
    -- 0️ ELIMINAR MOVCAJA Y SU MOVIMIENTO ASOCIADO
    -- =====================================================
    DELETE FROM movimiento
    WHERE id = (
        SELECT movimiento_id
        FROM movcaja
        WHERE id = 4417
    );

    DELETE FROM movcaja
    WHERE id = 4417;

    -- =====================================================
    -- 1️ AGRUPAR EXACTAMENTE COMO EN PHP
    -- =====================================================
    FOR grp IN
        SELECT DISTINCT
            mc.sucursal_id,
            mc.caja_id,
            mc.conceptopago_id
        FROM movcaja mc
        INNER JOIN movimiento m ON m.id = mc.movimiento_id
        WHERE mc.deleted_at IS NULL
          AND m.deleted_at IS NULL
    LOOP
        contador := 0;

        -- =================================================
        -- 2️ OBTENER MOVIMIENTOS EN ORDEN REAL DE CAJA
        -- =================================================
        FOR r IN
            SELECT m.id
            FROM movcaja mc
            INNER JOIN movimiento m ON m.id = mc.movimiento_id
            WHERE mc.sucursal_id = grp.sucursal_id
              AND mc.caja_id     = grp.caja_id
              AND (
                    (mc.conceptopago_id IS NULL AND grp.conceptopago_id IS NULL)
                 OR mc.conceptopago_id = grp.conceptopago_id
                  )
              AND mc.deleted_at IS NULL
              AND m.deleted_at IS NULL
            ORDER BY mc.created_at ASC, mc.id ASC, m.id ASC
        LOOP
            contador := contador + 1;

            -- =================================================
            -- 3️ ASIGNAR NÚMERO CORRELATIVO (00000001, etc)
            -- =================================================
            UPDATE movimiento
            SET numero = LPAD(contador::text, 8, '0')
            WHERE id = r.id;
        END LOOP;
    END LOOP;
END $$;



DO $$
DECLARE
    grp RECORD;     -- agrupación por sucursal
    r   RECORD;     -- cada movimiento
    contador INTEGER;
BEGIN
    -- =====================================================
    -- 1️ AGRUPAR POR SUCURSAL
    -- =====================================================
    FOR grp IN
        SELECT DISTINCT
            mh.sucursal_id
        FROM movhabitacion mh
        INNER JOIN movimiento m ON m.id = mh.movimiento_id
        WHERE mh.deleted_at IS NULL
          AND m.deleted_at IS NULL
    LOOP
        contador := 0;

        -- =================================================
        -- 2️ OBTENER MOVIMIENTOS EN ORDEN REAL DE CREACIÓN
        -- =================================================
        FOR r IN
            SELECT m.id
            FROM movhabitacion mh
            INNER JOIN movimiento m ON m.id = mh.movimiento_id
            WHERE mh.sucursal_id = grp.sucursal_id
              AND mh.deleted_at IS NULL
              AND m.deleted_at IS NULL
            ORDER BY mh.created_at ASC, mh.id ASC, m.id ASC
        LOOP
            contador := contador + 1;

            -- =================================================
            -- 3️ ASIGNAR NÚMERO CORRELATIVO
            -- =================================================
            UPDATE movimiento
            SET numero = LPAD(contador::text, 8, '0')
            WHERE id = r.id;
        END LOOP;
    END LOOP;
END $$;


DO $$
DECLARE
    grp RECORD;     -- agrupación por sucursal
    r   RECORD;     -- cada movimiento
    contador INTEGER;
BEGIN
    -- =====================================================
    -- 1️⃣ AGRUPAR POR SUCURSAL
    -- =====================================================
    FOR grp IN
        SELECT DISTINCT
            mr.sucursal_id
        FROM movreserva mr
        INNER JOIN movimiento m ON m.id = mr.movimiento_id
        WHERE mr.deleted_at IS NULL
          AND m.deleted_at IS NULL
    LOOP
        contador := 0;

        -- =================================================
        -- 2️⃣ OBTENER MOVIMIENTOS EN ORDEN REAL DE CREACIÓN
        -- =================================================
        FOR r IN
            SELECT m.id
            FROM movreserva mr
            INNER JOIN movimiento m ON m.id = mr.movimiento_id
            WHERE mr.sucursal_id = grp.sucursal_id
              AND mr.deleted_at IS NULL
              AND m.deleted_at IS NULL
            ORDER BY mr.created_at ASC,
                     mr.id ASC,
                     m.id ASC
        LOOP
            contador := contador + 1;

            -- =================================================
            -- 3️⃣ ASIGNAR NÚMERO CORRELATIVO
            -- =================================================
            UPDATE movimiento
            SET numero = LPAD(contador::text, 8, '0')
            WHERE id = r.id;
        END LOOP;
    END LOOP;
END $$;


SQL);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reversible migration.
    }
};
