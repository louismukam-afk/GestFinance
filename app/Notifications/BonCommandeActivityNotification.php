<?php

namespace App\Notifications;

use App\Models\bon_commandeok;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class BonCommandeActivityNotification extends Notification
{
    use Queueable;

    public function __construct(
        private bon_commandeok $bon,
        private string $action,
        private string $niveau,
        private ?string $motif = null,
        private ?string $acteur = null
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $isRefus = $this->action === 'refuse';
        $subject = ($isRefus ? 'Bon rejete' : 'Bon valide') . ' - ' . ($this->bon->nom_bon_commande ?? 'Bon de commande');
        $niveau = Str::upper($this->niveau);
        $acteur = $this->acteur ?: 'Utilisateur non precise';

        $mail = (new MailMessage)
            ->subject($subject)
            ->greeting('Bonjour ' . ($notifiable->name ?? ''))
            ->line('Une activite vient d etre effectuee sur votre bon de commande.')
            ->line('Bon : ' . ($this->bon->nom_bon_commande ?? 'N/A'))
            ->line('Niveau : ' . $niveau)
            ->line('Action : ' . ($isRefus ? 'Rejet' : 'Validation'))
            ->line('Effectue par : ' . $acteur)
            ->line('Montant : ' . number_format((float) $this->bon->montant_total, 0, ',', ' ') . ' FCFA');

        if ($isRefus && $this->motif) {
            $mail->line('Motif du rejet : ' . $this->motif);
        }

        return $mail
            ->line('Vous pouvez vous connecter a GestFinance pour consulter le suivi complet du bon.')
            ->salutation('Cordialement, ' . config('app.name', 'GestFinance'));
    }
}
