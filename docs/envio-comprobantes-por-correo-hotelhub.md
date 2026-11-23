# Envio de comprobantes por correo en HotelHUB

Este documento explica como HotelHUB envia comprobantes por correo y que se necesita para implementar el mismo metodo en otro sistema.

## Resumen rapido

HotelHUB no usa `Mail::send`, SMTP propio ni la configuracion `config/mail.php` para enviar comprobantes al cliente.

El flujo real es:

1. El usuario abre el modal de envio de correo desde la lista de comprobantes.
2. El sistema recibe `correo` y `venta_id`.
3. Laravel genera el PDF del comprobante con Snappy/wkhtmltopdf.
4. El PDF se convierte a texto UTF-8 para poder mandarlo dentro de JSON.
5. Se hace un `POST` HTTP con Guzzle al servicio externo:

```text
https://facturae-garzasoft.com/facturacion/enviaEmail/envia_correo_json.php
```

6. El servicio externo envia el correo con el PDF adjunto.

## Archivos actuales involucrados

- `routes/web.php`
  - `POST solicitud/enviarCorreo`
  - `GET enviarCorreo/modal/{id}`

- `app/Http/Controllers/SolicitudController.php`
  - `enviarCorreo(Request $request)`
  - `enviarCorreoModal($id)`

- `app/Http/Controllers/MovventumController.php`
  - `generarPDF($id, $returnContent = false)`

- `resources/views/solicitud/modalcorreo.blade.php`
  - Formulario que pide el correo del cliente.

- `resources/views/movventa/comprobante.blade.php`
  - Plantilla Blade usada para armar el PDF.

- `config/snappy.php`
  - Configuracion de wkhtmltopdf.

## Rutas actuales

```php
Route::post('solicitud/enviarCorreo', [SolicitudController::class, 'enviarCorreo'])
    ->name('enviarCorreo.cliente');

Route::get('enviarCorreo/modal/{id}', [SolicitudController::class, 'enviarCorreoModal'])
    ->name('comprobante.enviarCorreo');
```

El modal carga la solicitud por ID y envia:

```text
correo=<email del cliente>
venta_id=<id de movventa>
```

## Metodo actual de envio

Metodo fuente: `SolicitudController::enviarCorreo`.

Entrada:

```php
$request->validate([
    'correo' => 'required|email',
    'venta_id' => 'required|numeric',
]);
```

Generacion del PDF:

```php
$pdf = app(MovventumController::class)->generarPDF($venta_id, true);
$pdf = mb_convert_encoding($pdf, 'UTF-8', 'ISO-8859-1');
```

`generarPDF($venta_id, true)` devuelve el contenido binario del PDF usando:

```php
Pdf::loadView('movventa.comprobante', compact(...))->output();
```

Payload enviado al servicio externo:

```php
$fields = [
    "token" => "TOKEN_DEL_SERVICIO",
    "to" => [$correo],
    "bcc" => [],
    "cc" => [],
    "subject" => "Comprobante de Venta",
    "body" => "Evidencia comprobante electronico",
    "attachments" => [
        [
            "name_file" => "comprobante-{$venta_id}.pdf",
            "content_file" => $pdf,
            "content_type" => "application/pdf",
        ],
    ],
];

$client = new \GuzzleHttp\Client();
$response = $client->post($endpoint, [
    'json' => $fields,
]);
```

Importante: en Guzzle, la opcion `json` envia el cuerpo completo como JSON (`Content-Type: application/json`). No envia un campo llamado `json`.

Respuesta esperada:

```json
{
  "code": 0
}
```

Si el HTTP status no es `200`, falla. Si `code` es distinto de `0`, tambien falla.

## ENV recomendados para otro sistema

En HotelHUB el endpoint y el token estan hardcodeados. Para otro sistema conviene moverlos a `.env`.

Configura un token de integración en el entorno local:

```text
your-comprobantes-mail-token
```

```env
COMPROBANTES_MAIL_ENDPOINT=https://facturae-garzasoft.com/facturacion/enviaEmail/envia_correo_json.php
COMPROBANTES_MAIL_TOKEN=your-comprobantes-mail-token
COMPROBANTES_MAIL_SUBJECT="Comprobante de Venta"
COMPROBANTES_MAIL_BODY="Evidencia comprobante electronico"

SNAPPY_BINARY=C:/ruta/a/wkhtmltopdf.exe
SNAPPY_IMAGE_BINARY=C:/ruta/a/wkhtmltoimage.exe
```

En Linux puede ser algo como:

```env
SNAPPY_BINARY=/usr/local/bin/wkhtmltopdf-amd64
SNAPPY_IMAGE_BINARY=/usr/local/bin/wkhtmltoimage-amd64
```

Tambien se debe tener `APP_URL` correcto si el PDF usa imagenes absolutas, links publicos, XML/CDR o assets externos.

## Dependencias necesarias

En Laravel:

```bash
composer require guzzlehttp/guzzle barryvdh/laravel-snappy
```

Ademas se necesita instalar `wkhtmltopdf` en el servidor. Snappy solo es el wrapper PHP; el binario real debe existir y ser ejecutable.

Dependencias que ya usa HotelHUB:

```json
{
  "guzzlehttp/guzzle": "^7.2",
  "barryvdh/laravel-snappy": "^1.0"
}
```

Configuracion minima de `config/snappy.php`:

```php
'pdf' => [
    'enabled' => true,
    'binary' => env('SNAPPY_BINARY', '/usr/local/bin/wkhtmltopdf-amd64'),
    'timeout' => false,
    'options' => [
        'enable-local-file-access' => true,
        'allow' => [
            public_path(),
            storage_path('app/public'),
        ],
        'encoding' => 'UTF-8',
    ],
],
```

`enable-local-file-access` es importante porque el comprobante usa imagenes y CSS desde `public_path()`.

## Implementacion recomendada para otro Laravel

Crear un servicio para no repetir la logica en controladores.

```php
<?php

namespace App\Services;

use GuzzleHttp\Client;
use RuntimeException;

class ComprobanteEmailService
{
    public function __construct(private readonly Client $client = new Client())
    {
    }

    public function enviar(string $correo, string $fileName, string $pdfContent): void
    {
        $endpoint = config('services.comprobantes_mail.endpoint');
        $token = config('services.comprobantes_mail.token');

        if (!$endpoint || !$token) {
            throw new RuntimeException('Servicio de correo de comprobantes no configurado.');
        }

        $payload = [
            'token' => $token,
            'to' => [$correo],
            'bcc' => [],
            'cc' => [],
            'subject' => config('services.comprobantes_mail.subject', 'Comprobante de Venta'),
            'body' => config('services.comprobantes_mail.body', 'Evidencia comprobante electronico'),
            'attachments' => [
                [
                    'name_file' => $fileName,
                    'content_file' => mb_convert_encoding($pdfContent, 'UTF-8', 'ISO-8859-1'),
                    'content_type' => 'application/pdf',
                ],
            ],
        ];

        $response = $this->client->post($endpoint, ['json' => $payload]);

        if ($response->getStatusCode() !== 200) {
            throw new RuntimeException('Error HTTP enviando correo: ' . $response->getStatusCode());
        }

        $data = json_decode((string) $response->getBody());

        if (!isset($data->code) || (int) $data->code !== 0) {
            throw new RuntimeException('El servicio de correo rechazo el envio.');
        }
    }
}
```

Agregar a `config/services.php`:

```php
'comprobantes_mail' => [
    'endpoint' => env('COMPROBANTES_MAIL_ENDPOINT'),
    'token' => env('COMPROBANTES_MAIL_TOKEN'),
    'subject' => env('COMPROBANTES_MAIL_SUBJECT', 'Comprobante de Venta'),
    'body' => env('COMPROBANTES_MAIL_BODY', 'Evidencia comprobante electronico'),
],
```

Controlador ejemplo:

```php
use App\Services\ComprobanteEmailService;
use Illuminate\Http\Request;

public function enviarCorreo(Request $request, ComprobanteEmailService $emailService)
{
    $data = $request->validate([
        'correo' => ['required', 'email'],
        'venta_id' => ['required', 'integer', 'exists:movventa,id'],
    ]);

    $pdf = app(MovventumController::class)->generarPDF($data['venta_id'], true);
    $fileName = 'comprobante-' . $data['venta_id'] . '.pdf';

    $emailService->enviar($data['correo'], $fileName, $pdf);

    return response()->json([
        'success' => true,
        'message' => 'Correo enviado exitosamente.',
    ]);
}
```

## Generacion del PDF

El PDF debe generarse antes del envio. En HotelHUB el metodo `MovventumController::generarPDF($id, true)`:

- Busca la venta (`movventa`) con relaciones de cliente, sucursal, detalles, IGV y pagos.
- Calcula numero de comprobante.
- Arma datos para la vista.
- Renderiza `resources/views/movventa/comprobante.blade.php`.
- Devuelve `$pdf->output()` cuando `$returnContent = true`.

En otro sistema no es obligatorio copiar todo `MovventumController`; basta con tener un metodo equivalente:

```php
public function generarPDF(int $ventaId, bool $returnContent = false)
{
    $venta = Venta::with(['cliente', 'detalles', 'sucursal'])->findOrFail($ventaId);

    $pdf = Pdf::loadView('comprobantes.pdf', compact('venta'));

    if ($returnContent) {
        return $pdf->output();
    }

    return $pdf->stream("comprobante-{$ventaId}.pdf");
}
```

## Validaciones minimas antes de enviar

Implementar en backend:

- `correo` requerido y valido.
- `venta_id` existente.
- La venta debe pertenecer a la sucursal/empresa del usuario autenticado.
- La venta debe tener comprobante generado o datos suficientes para renderizar PDF.
- El PDF no debe estar vacio.
- El servicio externo debe responder HTTP `200` y `code = 0`.

## Diferencia con el envio electronico a SUNAT

No confundir estos dos flujos:

- Emision electronica: `FacturadorController::enviarFacturacionElectronica(...)` envia boleta/factura al WSDL del facturador/SUNAT y guarda ZIP XML/CDR.
- Envio por correo: `SolicitudController::enviarCorreo(...)` solo genera la representacion impresa en PDF y la manda por el endpoint de correo.

El correo no emite el comprobante. El comprobante debe existir o al menos estar correctamente registrado antes de enviarse al cliente.

## Payload final que debe replicarse

```json
{
  "token": "TOKEN_DEL_SERVICIO",
  "to": ["cliente@correo.com"],
  "bcc": [],
  "cc": [],
  "subject": "Comprobante de Venta",
  "body": "Evidencia comprobante electronico",
  "attachments": [
    {
      "name_file": "comprobante-123.pdf",
      "content_file": "CONTENIDO_DEL_PDF_EN_STRING_UTF8",
      "content_type": "application/pdf"
    }
  ]
}
```

## Checklist de implementacion

1. Instalar `guzzlehttp/guzzle`.
2. Instalar y configurar `barryvdh/laravel-snappy`.
3. Instalar el binario `wkhtmltopdf`.
4. Crear la plantilla PDF del comprobante.
5. Crear un metodo que retorne el PDF como string con `output()`.
6. Crear `COMPROBANTES_MAIL_ENDPOINT` y `COMPROBANTES_MAIL_TOKEN` en `.env`.
7. Crear servicio `ComprobanteEmailService`.
8. Crear ruta `POST` para enviar correo.
9. Validar correo, venta y permisos.
10. Probar con un comprobante real y revisar que el servicio responda `code = 0`.

## Notas de seguridad

- No hardcodear el token en el codigo nuevo.
- No permitir enviar comprobantes de otra sucursal/empresa.
- Registrar errores del servicio externo en logs.
- Considerar rate limit para evitar spam.
- Validar que el PDF generado corresponda al comprobante solicitado.
