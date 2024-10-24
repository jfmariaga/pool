<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CierreCajaNotificacion extends Notification
{
    use Queueable;

    protected $cierre;

    public function __construct($cierre)
    {
        $this->cierre = $cierre;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Cierre de Caja Realizado')
            ->line('Se ha realizado un cierre de caja con los siguientes detalles:')
            ->line('Fecha: ' . $this->cierre['fecha'])
            ->line('Total de Inicio: $' . number_format($this->cierre['total_inicio'], 2, '.', ','))
            ->line('Total de Cierre: $' . number_format($this->cierre['total_cierre'], 2, '.', ','))
            ->line('Total de Ventas: $' . number_format($this->cierre['total_ventas'], 2, '.', ','))
            ->line('Total de Compras: $' . number_format($this->cierre['total_compras'], 2, '.', ','))
            ->line('Total de Egresos: $' . number_format($this->cierre['total_egresos'], 2, '.', ','))
            ->line('Total de Ingresos Manuales: $' . number_format($this->cierre['total_ingresos'], 2, '.', ','))
            ->line('No responder este mensaje. Este mensaje fue enviado automáticamente por el sistema');
    }
}
