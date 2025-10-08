<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ClienteBienvenidaNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function via($notifiable) { return []; }

    public function sendToWhatsApp($notifiable)
    {
        if (!$notifiable->telefono) return;
        try {
            Http::withToken(env('WHATSAPP_TOKEN'))->post(
                'https://graph.facebook.com/v19.0/' . env('WHATSAPP_PHONE_NUMBER_ID') . '/messages',
                [
                    'messaging_product' => 'whatsapp',
                    'to' => $notifiable->telefono,
                    'type' => 'template',
                    'template' => [
                        'name' => 'bienvenida_cliente',
                        'language' => ['code' => 'es_MX'],
                        'components' => [[
                            'type' => 'body',
                            'parameters' => [['type' => 'text', 'text' => $notifiable->nombre]],
                        ]]
                    ]
                ]
            );
        } catch (\Exception $e) {
            Log::error('Error al enviar WhatsApp de bienvenida: ' . $e->getMessage());
        }
    }
}