<?php
namespace App\Notifications;

use App\Models\Venta;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VentaConfirmadaWhatsApp extends Notification implements ShouldQueue
{
    use Queueable;

    public Venta $venta;

    public function __construct(Venta $venta)
    {
        $this->venta = $venta;
    }

    public function via($notifiable) { return []; }

    public function sendToWhatsApp($notifiable)
    {
        if (!$notifiable->telefono) return;
        try {
            $this->venta->load('cliente');
            Http::withToken(env('WHATSAPP_TOKEN'))->post(
                'https://graph.facebook.com/v19.0/' . env('WHATSAPP_PHONE_NUMBER_ID') . '/messages',
                [
                    'messaging_product' => 'whatsapp',
                    'to' => $notifiable->telefono,
                    'type' => 'template',
                    'template' => [
                        'name' => 'confirmacion_de_venta',
                        'language' => ['code' => 'es_MX'],
                        'components' => [[
                            'type' => 'body',
                            'parameters' => [
                                ['type' => 'text', 'text' => $this->venta->cliente->nombre],
                                ['type' => 'text', 'text' => $this->venta->id_venta],
                                ['type' => 'text', 'text' => '$' . number_format($this->venta->total, 2)],
                            ]
                        ]]
                    ]
                ]
            );
        } catch (\Exception $e) {
            Log::error('Error al enviar WhatsApp de venta: ' . $e->getMessage());
        }
    }
}