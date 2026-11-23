# Envio de comprobante por WhatsApp en HotelHUB

Este documento resume la logica real que usa HotelHUB para enviar un comprobante por WhatsApp y sirve como guia para replicarla en otro sistema.

La implementacion actual usa la WhatsApp Cloud API de Meta a traves de `graph.facebook.com` y `GuzzleHttp\Client`.

## Resumen del flujo

1. El usuario abre el modal de WhatsApp desde la lista de comprobantes.
2. El modal pide el numero de celular.
3. El backend valida el `id`, `sucursal_id` y `celular`.
4. Se busca la `Solicitud` asociada al comprobante.
5. Si no existe `public_name`, se genera uno con UUID.
6. Se arma el PDF del comprobante usando `MovventumController::generarPDF($ventaId, true)`.
7. El PDF se sube primero a la API de Meta en `/{phone_number_id}/media`.
8. Con el `media_id` devuelto, se envia un mensaje tipo `template` por `/{phone_number_id}/messages`.
9. La respuesta se considera exitosa solo si WhatsApp acepta el mensaje.

## Archivos involucrados

- [`routes/web.php`](../routes/web.php)
  - Ruta `POST solicitud/enviarWhatsApp`
  - Ruta `GET enviarWhatsApp/modal/{id}`
- [`app/Http/Controllers/SolicitudController.php`](../app/Http/Controllers/SolicitudController.php)
  - `enviarWhatsAppModal($id)`
  - `enviarWhatsApp(Request $request)`
  - `asegurarPublicNameComprobante(Solicitud $comprobante)`
- [`resources/views/solicitud/modalwhatsapp.blade.php`](../resources/views/solicitud/modalwhatsapp.blade.php)
  - Formulario del modal.
- [`app/Http/Controllers/MovventumController.php`](../app/Http/Controllers/MovventumController.php)
  - `generarPDF($id, true)` para obtener el comprobante en PDF.

## Ruta actual

```php
Route::post('solicitud/enviarWhatsApp', [App\Http\Controllers\SolicitudController::class, 'enviarWhatsApp'])
    ->name('enviarWhatsApp.cliente');

Route::get('enviarWhatsApp/modal/{id}', [App\Http\Controllers\SolicitudController::class, 'enviarWhatsAppModal'])
    ->name('comprobante.enviarWhatsApp');
```

## Modal de envio

La vista [`resources/views/solicitud/modalwhatsapp.blade.php`](../resources/views/solicitud/modalwhatsapp.blade.php) envia:

- `id`
- `sucursal_id`
- `celular`

Tambien incluye un hidden con la ruta final:

```html
<input type="hidden" id="form-action-url" value="{{ route('enviarWhatsApp.cliente') }}">
```

## Validacion de entrada

El metodo actual valida:

```php
$request->validate([
    'celular' => 'required|numeric',
    'sucursal_id' => 'required|numeric',
    'id' => 'required|numeric',
]);
```

Si el comprobante no existe en esa sucursal, responde:

```php
return response()->json(['success' => false, 'errors' => 'Comprobante no encontrado'], 404);
```

## Datos que arma el backend

Con la `Solicitud` encontrada, el controlador construye estos valores:

```php
$facturador = json_decode($comprobante->json_facturador);
$sucursal = json_decode($comprobante->json_sucursal);
$persona = $comprobante->movimiento->Persona;

$razonsocial = $facturador->username_wsdl . ' - ' . $sucursal->razonsocial;
$cliente = $persona->nrodoc . ' - ' . ' ' . $persona->nombres . $persona->apellidos;
$numero = $comprobante->tipo_documento . $comprobante->serie . '-' . str_pad($comprobante->correlativo, 8, '0', STR_PAD_LEFT);
$archivo = $comprobante->nombre;
$nombrecomercial = $sucursal->nombrecomercial;
$firma = 'HotelHUB';
$downloadBillUrl = $comprobante->public_name . '/xml';
```

### Nota importante

Si `public_name` viene vacio, el sistema lo genera automaticamente con UUID antes de continuar.

## Generacion del PDF

El comprobante que se envia por WhatsApp no se arma manualmente. Se reutiliza el PDF ya existente:

```php
$ventaId = $comprobante->movimiento->movventa->id;
$pdfContent = app(\App\Http\Controllers\MovventumController::class)->generarPDF($ventaId, true);
```

Ese metodo devuelve el PDF en binario. Luego se escribe en un stream temporal para subirlo a Meta.

## APIs usadas hoy

### 1. Subida del archivo PDF

Endpoint:

```text
POST https://graph.facebook.com/v19.0/202127742990342/media
```

Headers:

```http
Authorization: Bearer {META_AUTH_TOKEN}
```

Multipart:

```php
[
    [
        'name' => 'file',
        'contents' => $stream,
        'filename' => $archivo . '.pdf',
        'headers' => [
            'Content-Type' => 'application/pdf'
        ]
    ],
    [
        'name' => 'messaging_product',
        'contents' => 'whatsapp'
    ]
]
```

Respuesta esperada:

```json
{
  "id": "MEDIA_ID"
}
```

### 2. Envio del mensaje

Endpoint:

```text
POST https://graph.facebook.com/v19.0/202127742990342/messages
```

Headers:

```http
Authorization: Bearer {META_AUTH_TOKEN}
Content-Type: application/json
```

Payload usado:

```php
$fields = [
    'messaging_product' => 'whatsapp',
    'to' => $telefono,
    'type' => 'template',
    'template' => [
        'name' => 'hotelhub_pdf_invoice',
        'language' => ['code' => 'es'],
        'components' => [
            [
                'type' => 'header',
                'parameters' => [
                    [
                        'type' => 'document',
                        'document' => [
                            'id' => $mediaId,
                            'filename' => $archivo . '.pdf'
                        ]
                    ]
                ]
            ],
            [
                'type' => 'body',
                'parameters' => [
                    ['type' => 'text', 'text' => $cliente],
                    ['type' => 'text', 'text' => $numero],
                    ['type' => 'text', 'text' => $razonsocial],
                    ['type' => 'text', 'text' => $nombrecomercial],
                    ['type' => 'text', 'text' => $firma],
                ]
            ],
            [
                'type' => 'button',
                'sub_type' => 'url',
                'index' => 0,
                'parameters' => [
                    ['type' => 'text', 'text' => $downloadBillUrl]
                ]
            ]
        ],
    ],
];
```

## Logica exacta de envio

1. Se crea un stream temporal con el binario del PDF.
2. Se sube ese archivo a `/{phone_number_id}/media`.
3. Se toma el `id` devuelto como `mediaId`.
4. Se envia el mensaje template `hotelhub_pdf_invoice`.
5. En el header del template se inserta el documento PDF usando `mediaId`.
6. El body recibe cinco variables de texto:
   - cliente
   - numero de comprobante
   - razon social
   - nombre comercial
   - firma
7. El boton URL usa `public_name/xml` para abrir el XML descargable.
8. Si la respuesta no trae `messages` o `messages[0].message_status !== 'accepted'`, el sistema lo toma como error.

## Respuesta esperada del backend

Exito:

```json
{
  "success": true,
  "message": "Mensaje enviado exitosamente por WhatsApp.",
  "redirect_url": "..."
}
```

Error al no ser aceptado por WhatsApp:

```json
{
  "success": false,
  "errors": "Mensaje no aceptado por WhatsApp"
}
```

Error general:

```json
{
  "success": false,
  "errors": "Error al enviar por WhatsApp: ..."
}
```

## Variables de entorno

Hoy el token se toma desde:

```php
$auth_token = env('META_AUTH_TOKEN');
```

Pero el `phone_number_id` y la version de API estan hardcodeados. Para llevarlo a otro sistema, conviene dejar todo esto en `env`:

```env
META_AUTH_TOKEN=tu_token_de_meta
META_WHATSAPP_PHONE_NUMBER_ID=202127742990342
META_WHATSAPP_API_VERSION=v19.0
META_WHATSAPP_API_BASE=https://graph.facebook.com
```

### Recomendacion minima de configuracion

```env
META_AUTH_TOKEN=...
META_WHATSAPP_PHONE_NUMBER_ID=...
META_WHATSAPP_API_VERSION=v19.0
META_WHATSAPP_API_BASE=https://graph.facebook.com
```

Y en `config/services.php`:

```php
'whatsapp' => [
    'token' => env('META_AUTH_TOKEN'),
    'phone_number_id' => env('META_WHATSAPP_PHONE_NUMBER_ID'),
    'version' => env('META_WHATSAPP_API_VERSION', 'v19.0'),
    'base_url' => env('META_WHATSAPP_API_BASE', 'https://graph.facebook.com'),
],
```

## Dependencias requeridas

El flujo usa:

- `guzzlehttp/guzzle`
- `barryvdh/laravel-snappy`
- `wkhtmltopdf` instalado en el servidor

## Lo que otro sistema debe replicar

Para integrar esta misma logica en otro sistema, hay que implementar estos pasos:

1. Obtener el comprobante emitible por ID.
2. Resolver la venta asociada.
3. Generar el PDF del comprobante.
4. Subir el PDF a la API de Meta como media.
5. Enviar un template WhatsApp con el `media_id`.
6. Pasar los textos de cliente, numero, razon social, nombre comercial y firma.
7. Exponer un modal o formulario que capture el celular.
8. Guardar el `public_name` o equivalente para poder construir el enlace al XML.

## Ejemplo de servicio reusable

Para otro sistema, lo ideal es encapsular esto en un servicio:

```php
class WhatsAppComprobanteService
{
    public function enviarComprobante(Solicitud $comprobante, string $telefono): array
    {
        // 1. Armar PDF
        // 2. Subir media
        // 3. Enviar template
        // 4. Retornar respuesta normalizada
    }
}
```

## Puntos a cuidar

- El telefono debe ir normalizado con prefijo internacional.
- Si la cuenta de Meta cambia el `phone_number_id`, hay que actualizarlo en `env`.
- El template `hotelhub_pdf_invoice` debe existir y estar aprobado en WhatsApp Manager.
- El nombre de las variables del template debe coincidir con el orden del payload.
- El PDF debe generarse antes de la subida.
- Si el PDF falla, no se debe intentar enviar el mensaje.

## Observacion sobre la implementacion actual

El codigo actual ya usa el token desde `META_AUTH_TOKEN`, pero mantiene hardcodeados:

- `202127742990342` como `phone_number_id`
- `v19.0` como version de API

Para llevarlo limpio a otro sistema, conviene mover esos dos valores a `env` y resolverlos desde `config/services.php`.

