<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class DocumentLookupController extends Controller
{
    public function dni(Request $request)
    {
        $validated = $request->validate(['dni' => ['required', 'digits:8']]);

        return $this->lookup(
            'https://facturae-garzasoft.com/facturacion/buscaCliente/BuscaCliente2.php',
            ['dni' => $validated['dni']]
        );
    }

    public function ruc(Request $request)
    {
        $validated = $request->validate(['ruc' => ['required', 'digits:11']]);

        return $this->lookup(
            'https://comprobante-e.com/facturacion/buscaCliente/BuscaClienteRuc.php',
            ['ruc' => $validated['ruc']]
        );
    }

    private function lookup(string $url, array $document): \Illuminate\Http\JsonResponse
    {
        $token = (string) config('services.comprobantes_mail.token');
        if ($token === '') {
            return response()->json(['message' => 'La consulta de documentos no está configurada.'], 503);
        }

        try {
            $response = Http::timeout(10)->get($url, $document + ['fe' => 'N', 'token' => $token]);

            return response()->json($response->json(), $response->status());
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json(['message' => 'No se pudo consultar el documento.'], 502);
        }
    }
}
