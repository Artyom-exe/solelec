<?php

namespace App\Services;

use App\Models\Intervention;
use App\Models\Quote;
use Carbon\Carbon;

class NotificationService
{
    /**
     * Récupère toutes les notifications urgentes
     */
    public static function getUrgentNotifications()
    {
        $notifications = [];

        // 1. Interventions pour aujourd'hui et demain
        $upcomingInterventions = Intervention::with(['client', 'devis'])
            ->where('status', '!=', 'terminée')
            ->where('status', '!=', 'annulée')
            ->whereBetween('date', [
                Carbon::today(),
                Carbon::tomorrow()->endOfDay()
            ])
            ->get();

        foreach ($upcomingInterventions as $intervention) {
            $isToday = Carbon::parse($intervention->date)->isToday();
            $clientName = $intervention->client->name . ' ' . $intervention->client->lastname;

            $notifications[] = [
                'id' => 'intervention_' . $intervention->id,
                'type' => 'intervention',
                'priority' => $isToday ? 'high' : 'medium',
                'title' => $isToday ? 'Intervention aujourd\'hui' : 'Intervention demain',
                'message' => "Intervention {$intervention->status} chez {$clientName}",
                'date' => $intervention->date,
                'url' => route('interventions.show', $intervention->id),
                'data' => $intervention
            ];
        }

        // 2. Devis en attente depuis plus de 7 jours
        $oldPendingQuotes = Quote::with(['client', 'services'])
            ->where('status', 'pending')
            ->where('created_at', '<=', Carbon::now()->subDays(7))
            ->get();

        foreach ($oldPendingQuotes as $quote) {
            $clientName = $quote->client->name . ' ' . $quote->client->lastname;
            $daysOld = Carbon::parse($quote->created_at)->diffInDays();

            $notifications[] = [
                'id' => 'quote_' . $quote->id,
                'type' => 'quote',
                'priority' => 'medium',
                'title' => 'Devis en attente',
                'message' => "Devis de {$clientName} en attente depuis {$daysOld} jours",
                'date' => $quote->created_at,
                'url' => route('devis.show', $quote->id),
                'data' => $quote
            ];
        }

        // 3. Nouveaux devis d'aujourd'hui
        $todayQuotes = Quote::with(['client', 'services'])
            ->where('status', 'pending')
            ->whereDate('created_at', Carbon::today())
            ->get();

        foreach ($todayQuotes as $quote) {
            $clientName = $quote->client->name . ' ' . $quote->client->lastname;

            $notifications[] = [
                'id' => 'new_quote_' . $quote->id,
                'type' => 'new_quote',
                'priority' => 'high',
                'title' => 'Nouveau devis',
                'message' => "Nouvelle demande de devis de {$clientName}",
                'date' => $quote->created_at,
                'url' => route('devis.show', $quote->id),
                'data' => $quote
            ];
        }

        // Trier par priorité puis par date
        usort($notifications, function($a, $b) {
            $priorityOrder = ['high' => 3, 'medium' => 2, 'low' => 1];
            $aPriority = $priorityOrder[$a['priority']] ?? 1;
            $bPriority = $priorityOrder[$b['priority']] ?? 1;

            if ($aPriority === $bPriority) {
                return strtotime($b['date']) - strtotime($a['date']);
            }

            return $bPriority - $aPriority;
        });

        return $notifications;
    }

    /**
     * Compte le nombre de notifications urgentes
     */
    public static function getUrgentNotificationsCount()
    {
        $notifications = self::getUrgentNotifications();
        return count(array_filter($notifications, function($notif) {
            return $notif['priority'] === 'high';
        }));
    }

    /**
     * Compte le total des notifications
     */
    public static function getTotalNotificationsCount()
    {
        return count(self::getUrgentNotifications());
    }
}
