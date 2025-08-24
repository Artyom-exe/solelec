<?php

namespace App\Services;

use App\Models\Intervention;
use App\Models\Quote;
use Carbon\Carbon;
use App\Models\NotificationRead;

class NotificationService
{
    /**
     * Récupère toutes les notifications urgentes pour un utilisateur
     */
    public static function getUrgentNotifications($userId = null)
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
                'date' => $intervention->updated_at ?? $intervention->date,
                'url' => route('interventions.show', $intervention->id),
                'data' => $intervention,
                'notification_id' => $intervention->id
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
                'date' => $quote->updated_at ?? $quote->created_at,
                'url' => route('devis', $quote->id),
                'data' => $quote,
                'notification_id' => $quote->id
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
                'date' => $quote->updated_at ?? $quote->created_at,
                'url' => route('devis', $quote->id),
                'data' => $quote,
                'notification_id' => $quote->id
            ];
        }

        // Ajouter le statut de lecture pour chaque notification
        if ($userId) {
            $readNotifications = NotificationRead::where('user_id', $userId)
                ->get()
                ->keyBy(function ($item) {
                    return $item->notification_type . '_' . $item->notification_id;
                });
            foreach ($notifications as &$notification) {
                // Utilise bien notification_type et notification_id pour la clé
                $key = $notification['type'] . '_' . $notification['notification_id'];
                $notification['is_read'] = $readNotifications->has($key);
            }
            unset($notification);
        }

        // Tri strictement chronologique : non lues en haut, puis lues, le tout du plus récent au moins récent
        usort($notifications, function($a, $b) {
            $aRead = $a['is_read'] ?? false;
            $bRead = $b['is_read'] ?? false;
            if ($aRead !== $bRead) {
                return $aRead ? 1 : -1;
            }
            // Plus récent en haut
            return strtotime($b['date']) - strtotime($a['date']);
        });
        return $notifications;
    }

    /**
     * Compte le nombre de notifications urgentes pour un utilisateur
     */
    public static function getUrgentNotificationsCount($userId = null)
    {
        $notifications = self::getUrgentNotifications($userId);
        return count(array_filter($notifications, function($notif) {
            return $notif['priority'] === 'high';
        }));
    }

    /**
     * Compte le total des notifications pour un utilisateur
     */
    public static function getTotalNotificationsCount($userId = null)
    {
        $notifications = self::getUrgentNotifications($userId);
        return count(array_filter($notifications, function($notif) {
            return empty($notif['is_read']) || $notif['is_read'] === false;
        }));
    }

    /**
     * Marquer une notification comme lue (utilise la table)
     */
    public static function markAsRead($userId, $notificationType, $notificationId)
    {
        return NotificationRead::updateOrCreate(
            [
                'user_id' => $userId,
                'notification_type' => $notificationType,
                'notification_id' => $notificationId,
            ],
            [
                'read_at' => Carbon::now(),
            ]
        );
    }

    /**
     * Marquer toutes les notifications comme lues pour un utilisateur
     */
    public static function markAllAsRead($userId)
    {
        \Log::info('[Notification] markAllAsRead called', ['user_id' => $userId]);
        $notifications = self::getUrgentNotifications(); // Sans filtrage par utilisateur pour obtenir toutes les notifications
        foreach ($notifications as $notification) {
            \Log::info('[Notification] markAllAsRead - notification', [
                'user_id' => $userId,
                'type' => $notification['type'],
                'notification_id' => $notification['notification_id']
            ]);
            self::markAsRead($userId, $notification['type'], $notification['notification_id']);
        }
        return true;
    }
}

