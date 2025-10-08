<?php
namespace App\Observers;

use App\Models\Cliente;
use App\Notifications\ClienteBienvenidaNotification;

class ClienteObserver
{
    public function created(Cliente $cliente): void
    {
        (new ClienteBienvenidaNotification())->sendToWhatsApp($cliente);
    }
}