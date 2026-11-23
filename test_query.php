<?php
echo json_encode(\DB::select("WITH SolicitudesCalculadas AS (
    SELECT 
        s.id AS solicitud_id,
        s.fecha,
        s.sucursal_id,
        (s.tipo_documento || s.serie || '-' || LPAD(s.correlativo::text, 8, '0')) AS comprobante,
        suc.ruc AS sucursal_ruc,
        suc.nombrecomercial AS sucursal_nombre,
        s.total AS monto_total_solicitud, 
        SUM(
            (detalle->>'cantidad')::numeric * (detalle->>'precioventaunitarioxitem')::numeric 
            - COALESCE((detalle->>'descuentoxitem')::numeric, 0)
        ) AS monto_total_detalles,
        string_agg(
            (detalle->>'cantidad') || 'x ' || (detalle->>'descripcion') || ' (Precio: ' || (detalle->>'precioventaunitarioxitem') || ', Desc: ' || COALESCE(detalle->>'descuentoxitem', '0') || ')', 
            ' | '
        ) AS detalles_legibles
    FROM 
        solicitud s
    LEFT JOIN 
        sucursal suc ON s.sucursal_id = suc.id, 
        LATERAL json_array_elements((s.data::json)->'detalles') AS detalle 
    WHERE 
        s.data IS NOT NULL 
        AND s.data::text LIKE '%detalles%' 
    GROUP BY 
        s.id,
        s.fecha,
        s.sucursal_id,
        s.tipo_documento,
        s.serie,
        s.correlativo,
        suc.ruc,
        suc.nombrecomercial,
        s.total
)
SELECT 
    solicitud_id,
    fecha,
    sucursal_id,
    comprobante,
    sucursal_ruc,
    sucursal_nombre,
    monto_total_solicitud,
    monto_total_detalles,
    (monto_total_solicitud - monto_total_detalles) AS diferencia,
    detalles_legibles
FROM 
    SolicitudesCalculadas
WHERE 
    monto_total_solicitud != monto_total_detalles 
ORDER BY 
    fecha DESC, solicitud_id DESC LIMIT 2;"));
