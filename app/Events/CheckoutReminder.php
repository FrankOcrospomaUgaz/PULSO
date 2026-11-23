<?php

namespace App\Events;

use App\Models\MovhabitacionHabitacion;
use Carbon\Carbon;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CheckoutReminder implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $habitacion_id;
    public string $habitacion_numero;
    public string $fechacheckout;
    public int $sucursal_id;

    public function __construct(MovhabitacionHabitacion $detalle)
    {
        $this->habitacion_id = $detalle->habitacion_id;
        $this->habitacion_numero = $detalle->habitacion?->numero ?? (string) $detalle->habitacion_id;
        $checkout = $detalle->movhabitacion?->fechasalida ?? $detalle->fechacheckout;
        $this->fechacheckout = $checkout
            ? Carbon::parse($checkout)->format('Y-m-d H:i')
            : '';
        $this->sucursal_id = (int) ($detalle->movhabitacion?->sucursal_id
            ?? $detalle->habitacion?->sucursal_id
            ?? 0);
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('checkout-reminders.' . $this->sucursal_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'checkout.reminder';
    }

    public function broadcastWith(): array
    {
        return [
            'habitacion_id' => $this->habitacion_id,
            'habitacion_numero' => $this->habitacion_numero,
            'fechacheckout' => $this->fechacheckout,
            'sucursal_id' => $this->sucursal_id,
        ];
    }
}
