# Facturación electrónica en HotelHUB

Este documento consolidó el módulo actual de facturación electrónica del proyecto para que pueda servir como referencia de arquitectura, lógica de negocio, tablas, rutas, payloads y reglas para implementar un flujo similar en otro sistema que también emita comprobantes a SUNAT.

## 1. Resumen ejecutivo

HotelHUB implementa facturación electrónica mediante:

- Un módulo de configuración de facturadores por sucursal.
- Un registro central de solicitudes de comprobantes en la tabla `solicitud`.
- Un flujo que toma una venta (`movventa`) y la convierte en un comprobante electrónico.
- Un envío al Web Service del facturador mediante SOAP.
- Almacenamiento del ZIP con XML y CDR.
- Descarga y sincronización de estados con SUNAT.
- Reenvío de pendientes, anulaciones y resúmenes de baja.

El sistema actual está pensado para emitir:

- Boletas
- Facturas
- Tickets (sin emisión electrónica)
- Anulaciones / bajas de comprobantes

## 2. Alcance funcional actual

### 2.1 Módulos involucrados

- Módulo de ventas: `movventa`
- Módulo de caja: `movcaja`
- Módulo de solicitudes/comprobantes: `solicitud`
- Módulo de configuración de facturadores: `facturador`
- Módulo de clientes/personas: `persona` y `personamaestro`
- Módulo de sucursales: `sucursal`
- Módulo de tipos de documento: `tipodocumento`
- Módulo de impuestos: `igv`

### 2.2 Acciones soportadas

- Emitir comprobante al momento de registrar una venta
- Reintentar envíos pendientes de boletas/facturas
- Sincronizar estados con el servidor del facturador/SUNAT
- Descargar XML y CDR
- Enviar por correo/WhatsApp
- Generar PDF del comprobante
- Anular boletas/facturas mediante resumen de baja
- Descargar XML/CDR por solicitud

## 3. Arquitectura actual

### 3.1 Controladores principales

- `app/Http/Controllers/FacturadorController.php`
  - Contiene la lógica central de armado del payload y envío al Web Service.
  - Método clave: `enviarFacturacionElectronica(...)`

- `app/Http/Controllers/SolicitudController.php`
  - Gestiona solicitudes, reenvíos, sincronización, anulación, descarga de XML/CDR y reportes.

- `app/Http/Controllers/MovventumController.php`
  - Procesa ventas, convierte ventas a comprobantes y genera PDF/impresión.

- `app/Http/Controllers/MovhabitacionController.php`
  - En algunos flujos, también dispara la facturación desde consumo de alojamiento/habitaciones.

- `app/Http/Controllers/CompromisoPagoController.php`
  - En algunos caminos de pagos también dispara la facturación.

### 3.2 Flujo técnico general

1. Se registra una venta (`movventa`) y su movimiento asociado (`movimiento`).
2. Se arma el payload del comprobante con los datos del cliente, sucursal, detalles, forma de pago y tipo de documento.
3. Se crea una fila en `solicitud` con estado pendiente.
4. Se envía al Web Service del facturador vía SOAP.
5. Si responde correctamente, se guarda el ZIP con el XML y el CDR.
6. Se actualiza la solicitud con `solicitud_facturador_id`, nombre del archivo, estado y timestamps.
7. Se puede luego descargar XML/CDR, generar PDF o sincronizar el estado con el servidor.

## 4. Tablas y modelos clave

### 4.1 `facturador`

Tabla que almacena la configuración del facturador por sucursal.

Campos principales:

- `id`
- `empresa_facturador_id`
- `porcentaje_igv`
- `username_wsdl`
- `password_wsdl`
- `modo_autenticacion`
- `username_sunat`
- `password_sunat`
- `situacion`
- `sucursal_id`

Modelo:

- `app/Models/Facturador.php`

Uso:

- Contiene credenciales y configuración para emitir comprobantes.
- Se usa para enviar al servicio del facturador.

### 4.2 `solicitud`

Tabla central para trazabilidad del flujo de facturación electrónica.

Campos principales:

- `id`
- `tipo_solicitud` (por ejemplo: `sendBill`, `sendSummary`)
- `data` (payload JSON del comprobante o resumen de baja)
- `fecha`
- `fechaenvio`
- `fecharespuesta`
- `fechaanulacion`
- `estado` (E, R, M, T, I, U, V, X, etc.)
- `nombre` (nombre del archivo/ZIP generado)
- `total`
- `moneda`
- `tipo_documento` (B/F/A/E)
- `serie`
- `correlativo`
- `ticket_solicitud`
- `persona_id`
- `json_persona`
- `sucursal_id`
- `json_sucursal`
- `facturador_id`
- `json_facturador`
- `movimiento_id`
- `solicitud_facturador_id`
- `error_code`
- `error_text`
- `public_name`

Modelo:

- `app/Models/Solicitud.php`

### 4.3 `movventa`

Representa la venta que puede convertirse en comprobante.

Campos principales:

- `id`
- `json_sucursal`
- `serie`
- `anio`
- `tipo_detalle`
- `consumo`
- `tipo_pago`
- `estado`
- `tipo_venta`
- `moneda`
- `tipocambio`
- `subtotal`
- `igv`
- `total`
- `movimiento_id`
- `sucursal_id`

Modelo:

- `app/Models/Movventum.php`

### 4.4 `det_movventa`

Detalles (líneas) del comprobante/venta.

Campos principales:

- `id`
- `movventa_id`
- `producto_id`
- `codigo`
- `descripcion`
- `cantidad`
- `monto`
- `monto_original`
- `igv_id`
- `unidad_id`
- `json_producto`

### 4.5 `movimiento`

Entidad genérica de movimiento contable / transaccional.

Campos importantes:

- `id`
- `numero`
- `fecha`
- `usuario_id`
- `persona_id`
- `tipomovimiento_id`
- `tipodocumento_id`
- `sucursal_id`
- `situacion`

### 4.6 `persona` / `personamaestro`

Clientes/usuarios del sistema.

Campos relevantes:

- `nrodoc`
- `tipopersona`
- `nombres`
- `apellidos`
- `direccion`

### 4.7 `caja` / `movcaja` / `det_movcaja`

Se usan para rastrear pagos y formas de pago asociadas a la venta.

Campos relevantes:

- `caja_id`
- `movimiento_id`
- `sucursal_id`
- `conceptopago_id`
- `monto`
- `mediopago_id`
- `tipo`
- `fechavencimiento`

## 5. Reglas de negocio actuales

### 5.1 Tipos de documento

El sistema usa el tipo de documento para decidir qué Web Service usar:

- `B` => Boleta
- `F` => Factura
- `A` => Anulación / baja
- `E` => Resumen de baja / anulaciones

### 5.2 Validaciones de frontend

En [public/js/app.js](public/js/app.js) se aplican reglas antes de enviar la venta:

- Si el tipo de documento es factura (`10`), se exige que el cliente tenga RUC válido de 11 dígitos.
- Si el tipo de documento es boleta (`4`) y el cliente usa documento `0`, no se permite emitir cuando el monto total supera S/ 700.
- No se permite emitir comprobantes tributos con monto total menor o igual a 0 salvo excepciones específicas.
- Se valida que exista un cliente y una caja activa.
- Se valida que la suma de medios de pago coincida con el total a pagar.
- Si el detalle es tipo `GLOSA`, se exige descripción obligatoria para evitar rechazo por SUNAT.

### 5.3 Reglas de emisión

- Para boletas, se emite a través de `wsdl_boleta_rc.php`.
- Para facturas, se emite a través de `wsdl_factura_rc.php`.
- Si la venta es pago a crédito, se arma el bloque `cuotas`.
- El `tipo de pago` se convierte a `C` (contado) o `D` (crédito).
- El sistema arma el `comprobante` como JSON con detalles, fechas, datos del cliente, forma de pago, etc.

## 6. Payload de comprobante armado por HotelHUB

El payload principal se construye en `FacturadorController::enviarFacturacionElectronica(...)`.

### 6.1 Estructura básica

```json
{
  "numeroboleta": "B001-000001",
  "fechaemision": "2026-07-09",
  "horaemision": "14:30:00",
  "usuario": "Juan Perez",
  "codubigeo": "0000",
  "tipodoc": "1",
  "dni": "12345678",
  "moneda": "PEN",
  "descuentototal": "0",
  "percepcion": "",
  "aplicacionpercepcion": "",
  "documentosanexos": [],
  "detalles": [
    {
      "tipodetalle": "V",
      "codigo": "-",
      "unidadmedida": "NIU",
      "cantidad": 1,
      "descripcion": "POR CONSUMO",
      "precioventaunitarioxitem": 120.5,
      "descuentoxitem": "0",
      "tipoigv": "10",
      "tasaisc": "0",
      "aplicacionisc": "",
      "precioventasugeridoxitem": ""
    }
  ],
  "formapago": "C",
  "cuotas": [],
  "porcentajeigv": 18
}
```

### 6.2 Estructura de detalle para venta detallada

Cada detalle suele incluir:

- `codigo`
- `unidadmedida`
- `cantidad`
- `descripcion`
- `precioventaunitarioxitem`
- `descuentoxitem`
- `tipoigv`

## 7. Integración con los Web Services

### 7.1 Endpoints utilizados

El sistema actual apunta a:

- `http://157.245.85.164/facturacion/wsdl/wsdl_boleta_rc.php`
- `http://157.245.85.164/facturacion/wsdl/wsdl_factura_rc.php`
- `http://157.245.85.164/facturacion/wsdl/wsdl_comprobantes.php`
- `http://157.245.85.164/facturacion/wsdl/wsdl_comunicacionbajas.php`
- `http://157.245.85.164/facturacion/wsdl/wsdl_resumenboletas.php`

### 7.2 Llamada SOAP actual

Se usa `nusoap_client` y se envían datos como:

```php
$datos = [
    'ruc' => $user_wsdl,
    'password' => $pass_wsdl,
    'json' => $datos_enviar
];
```

Donde `json` contiene un objeto serializado con:

- `token`
- `serieboleta` o `seriefactura`
- `correlativoboleta` o `correlativofactura`
- `doc`
- `nombre`
- `direccion`
- `total`
- `comprobante`

### 7.3 Respuesta esperada del servicio

El flujo espera respuestas con campos como:

- `code`
- `mensaje`
- `id_solicitud`
- `nombre_documento`
- `fileZIPBASE64`

## 8. Flujo de emisión de un comprobante

### 8.1 Emitir una boleta o factura desde una venta

1. El usuario registra la venta.
2. El sistema valida los datos del cliente y forma de pago.
3. Se crea o selecciona el `facturador` de la sucursal.
4. Se arma el JSON con los detalles del comprobante.
5. Se crea la fila en `solicitud` con `tipo_solicitud = sendBill`.
6. Se invoca a `FacturadorController::enviarFacturacionElectronica(...)`.
7. Se llama al WS del facturador.
8. Si todo va bien, se guarda el ZIP con XML/CDR.
9. Se devuelve una respuesta al frontend con el resultado y URL de PDF.

### 8.2 Reenvío de pendientes

El sistema tiene rutas y métodos para reenviar boletas y facturas pendientes:

- `send_pending_boletas()`
- `send_pending_facturas()`

Estas búsquedas las solicitudes con estado `E` y vuelven a llamar al mismo método de envío.

## 9. Sincronización con SUNAT / servidor del facturador

### 9.1 Método `sync()`

`SolicitudController::sync()` consulta los comprobantes enviados previamente y actualiza el estado local según la respuesta del servidor.

Estados mapeados:

- `C` => correcto, se marca como `T` (boleta) o `M` (factura) / `U` para otros.
- `I` => incorrecto, se marca como `I` o `V` según el tipo.
- `B` => baja / anulación
- `E` => problema con comprobante / reintentar

### 9.2 Descarga de XML y CDR

Rutas actuales:

- `/solicitud/{id}/download-xml`
- `/solicitud/{id}/download-cdr`

El sistema:

- Busca el ZIP del comprobante en almacenamiento local.
- Extrae el XML o CDR desde el ZIP.
- Si no existe el CDR en el ZIP, lo intenta recuperar desde el servicio del facturador.

## 10. Anulación y resúmenes de baja

### 10.1 Anulación de boletas

- Se crean resúmenes con `tipo_solicitud = sendSummary`.
- Se envían a `wsdl_resumenboletas.php`.
- Se incluyen IDs de comprobantes a anular, motivo y números.

### 10.2 Anulación de facturas

- Se arma un resumen de baja.
- Se envía a `wsdl_comunicacionbajas.php`.
- Se usa el número de baja y los detalles de cada comprobante.

### 10.3 Estados relevantes

- `E` => pendiente / en espera
- `R` => enviado / registrado en proceso
- `M` => aceptado / emitido (boleta)
- `T` => aceptado / emitido (ticket)
- `I` => incorrecto
- `U` => anulación / baja aceptada o relacionada
- `V` => baja incorrecta / rechazada
- `X` => error

## 11. Generación de PDF y comprobante visual

El sistema genera PDF para mostrar el comprobante en pantalla o para imprimir:

- `MovventumController::generarPDF(...)`
- `MovventumController::imprimirComprobante(...)`

Se usa:

- `snappy` / `wkhtmltopdf`
- Vistas Blade de comprobantes
- QR con información del comprobante

## 12. Rutas relevantes del proyecto

Rutas principales en [routes/web.php](routes/web.php):

- `Route::resource('facturador', ...)`
- `Route::resource('solicitud', ...)`
- `Route::resource('movventa', ...)`
- `Route::get('solicitud/{id}/download-xml', ...)`
- `Route::get('solicitud/{id}/download-cdr', ...)`
- `Route::get('solicitudsend_pending_boletas', ...)`
- `Route::get('solicitudsend_pending_facturas', ...)`
- `Route::get('solicitudsync', ...)`
- `Route::get('solicitudcancell_boletas', ...)`
- `Route::get('solicitudcancell_facturas', ...)`
- `Route::post('solicitud/cancellboletas', ...)`
- `Route::post('solicitud/cancellfacturas', ...)`

## 13. Recomendación para implementar lo mismo en otro sistema

Si el otro sistema debe emitir comprobantes a SUNAT, la arquitectura recomendada es:

### 13.1 Base de datos mínima

- `facturadores`
- `solicitudes`
- `clientes`
- `ventas`
- `detalle_ventas`
- `sucursales`
- `series`
- `xml_archivos`

### 13.2 Servicios recomendados

1. `ComprobanteService`
   - arma el payload del comprobante
   - decide tipo de documento
   - valida cliente / detalles / monto

2. `SunatClient`
   - envía el comprobante al WS del facturador o a SUNAT directamente
   - maneja SOAP / HTTP
   - procesa respuesta

3. `SolicitudRepository`
   - guarda trazabilidad
   - registra estado, errores, IDs del servidor y archivos

4. `StorageService`
   - almacena XML, CDR y PDF

### 13.3 Flujo recomendado para el otro sistema

```text
1. Crear venta
2. Validar datos del cliente y monto
3. Armar payload de comprobante
4. Crear registro de solicitud
5. Enviar al WS del facturador/SUNAT
6. Guardar respuesta y archivos
7. Actualizar estado local
8. Descargar XML/CDR/PDF
```

### 13.4 Payload mínimo recomendado

```json
{
  "tipo_documento": "F",
  "serie": "F001",
  "correlativo": 1,
  "cliente": {
    "tipo_doc": "6",
    "numero_doc": "20123456789",
    "nombre": "Empresa Demo",
    "direccion": "Av. Principal 123"
  },
  "detalles": [
    {
      "descripcion": "Servicio",
      "cantidad": 1,
      "precio_unitario": 100,
      "tipo_igv": "10"
    }
  ],
  "total": 118,
  "moneda": "PEN"
}
```

## 14. Puntos críticos a tener en cuenta

- Los comprobantes deben haber pasado validaciones de negocio antes del envío.
- Los estados de `solicitud` son clave para controlar reintentos y sincronización.
- No confíar solo en el frontend; la validación fuerte debe ir en backend.
- El almacenamiento local de los ZIP/XML/CDR es esencial para re-descarga posterior.
- El sistema debe manejar correctamente errores de SUNAT, respuesta nula, fallas de red y tiempos de espera.

## 15. Resumen corto para pasar al otro sistema

El módulo actual hace lo siguiente:

- Toma una venta y la convierte en comprobante electrónico.
- Usa una configuración de `facturador` por sucursal.
- Registra la operación en `solicitud`.
- Envia un JSON del comprobante al Web Service del facturador/SUNAT.
- Guarda ZIP con XML y CDR.
- Permite sincronizar estados, descargar archivos, reenviar pendientes y anular comprobantes.

Si el otro sistema debe replicarlo, lo más importante es implementar:

- un modelo de ventas con detalles,
- un modelo de solicitudes de facturación,
- un cliente SOAP/HTTP para el Web Service,
- un almacenamiento de XML/CDR,
- un estado de flujo y reintentos,
- y validaciones previas estrictas.
