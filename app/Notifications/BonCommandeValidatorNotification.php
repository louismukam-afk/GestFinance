<?php

namespace App\Notifications;

use App\Models\bon_commandeok;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BonCommandeValidatorNotification extends Notification
{
    use Queueable;

    public function __construct(
        private bon_commandeok $bon,
        private string $niveau,
        private ?string $acteur = null
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Bon a traiter - Validation ' . $this->niveau)
            ->greeting('Bonjour ' . ($notifiable->name ?? ''))
            ->line('L emetteur vient de valider un bon de commande qui peut necessiter votre intervention.')
            ->line('Bon : ' . ($this->bon->nom_bon_commande ?? 'N/A'))
            ->line('Niveau concerne : ' . $this->niveau)
            ->line('Emetteur : ' . ($this->acteur ?: ($this->bon->user->name ?? 'N/A')))
            ->line('Montant : ' . number_format((float) $this->bon->montant_total, 0, ',', ' ') . ' FCFA')
            ->line('Veuillez vous connecter a GestFinance pour consulter et traiter le bon.')
            ->salutation('Cordialement, ' . config('app.name', 'GestFinance'));
    }
}
