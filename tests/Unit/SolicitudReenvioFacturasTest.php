<?php

use App\Http\Controllers\FacturadorController;
use App\Http\Controllers\SolicitudController;
use App\Models\Facturador;
use App\Models\Solicitud;

function invocarMetodoPrivado(object $objeto, string $metodo, array $argumentos = [])
{
    $reflexion = new ReflectionMethod($objeto, $metodo);
    $reflexion->setAccessible(true);

    return $reflexion->invokeArgs($objeto, $argumentos);
}

test('clasifica correctamente el resultado de un reenvío de facturas', function (
    int $total,
    int $reenviadas,
    int $noReenviadas,
    string $estadoEsperado,
) {
    $controller = new SolicitudController;

    expect(invocarMetodoPrivado(
        $controller,
        'estadoResultadoReenvio',
        [$total, $reenviadas, $noReenviadas],
    ))->toBe($estadoEsperado);
})->with([
    'sin pendientes' => [0, 0, 0, 'empty'],
    'todas reenviadas' => [2, 2, 0, 'success'],
    'resultado parcial' => [3, 2, 1, 'partial'],
    'todas rechazadas' => [2, 0, 2, 'error'],
]);

test('oculta las credenciales al informar un error del facturador', function () {
    $controller = new SolicitudController;
    $facturador = new Facturador([
        'username_wsdl' => '20501110651',
        'password_wsdl' => 'clave-secreta',
    ]);

    $mensaje = invocarMetodoPrivado($controller, 'mensajeRespuestaFacturador', [[
        'success' => false,
        'message' => 'SOAP Error',
        'details' => 'Falló 20501110651 con clave-secreta',
    ], $facturador]);

    expect($mensaje)
        ->toContain('SOAP Error')
        ->toContain('***')
        ->not->toContain('20501110651')
        ->not->toContain('clave-secreta');
});

test('genera un mensaje claro para un resultado parcial', function () {
    $controller = new SolicitudController;

    expect(invocarMetodoPrivado(
        $controller,
        'mensajeResultadoReenvio',
        [3, 2, 1],
    ))->toBe('Se reenviaron 2 de 3 facturas; 1 no pudo enviarse.');
});

test('recupera los detalles originales guardados para el reenvío', function () {
    $controller = new FacturadorController;
    $solicitud = new Solicitud([
        'data' => json_encode([
            'numerofactura' => '036-00000405',
            'ruc' => '20131378972',
            'detalles' => [
                [
                    'descripcion' => 'HOSPEDAJE',
                    'cantidad' => 1,
                    'precioventaunitarioxitem' => 320,
                ],
            ],
        ]),
    ]);

    $comprobante = invocarMetodoPrivado(
        $controller,
        'comprobanteGuardado',
        [$solicitud],
    );

    expect($comprobante['detalles'])
        ->toHaveCount(1)
        ->and($comprobante['detalles'][0]['descripcion'])->toBe('HOSPEDAJE')
        ->and($comprobante['detalles'][0]['precioventaunitarioxitem'])->toBe(320);
});

test('descarta un comprobante guardado con json inválido', function () {
    $controller = new FacturadorController;
    $solicitud = new Solicitud(['data' => '{json-invalido']);

    expect(invocarMetodoPrivado(
        $controller,
        'comprobanteGuardado',
        [$solicitud],
    ))->toBeNull();
});
