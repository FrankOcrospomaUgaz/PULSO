<?php

use App\Http\Controllers\MovventumController;
use App\Models\Movimiento;
use App\Models\Movventum;
use App\Models\Solicitud;
use Carbon\Carbon;

function resolverFechaPdf(Movventum $movventa, ?Solicitud $solicitud): Carbon
{
    $metodo = new ReflectionMethod(MovventumController::class, 'resolverFechaEmisionComprobante');
    $metodo->setAccessible(true);

    return $metodo->invoke(new MovventumController, $movventa, $solicitud);
}

test('el comprobante usa la fecha de la solicitud', function () {
    $movimiento = new Movimiento(['fecha' => '2026-09-22 15:30:00']);
    $movventa = new Movventum;
    $movventa->setRelation('movimiento', $movimiento);

    $solicitud = new Solicitud(['fecha' => '2026-09-20 11:34:31']);

    expect(resolverFechaPdf($movventa, $solicitud)->format('Y-m-d H:i:s'))
        ->toBe('2026-09-20 11:34:31');
});

test('el ticket sin solicitud usa la fecha del movimiento de venta', function () {
    $movimiento = new Movimiento(['fecha' => '2026-09-22 15:30:00']);
    $movventa = new Movventum;
    $movventa->setRelation('movimiento', $movimiento);

    expect(resolverFechaPdf($movventa, null)->format('Y-m-d H:i:s'))
        ->toBe('2026-09-22 15:30:00');
});
