<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Récupère toutes les notifications urgentes pour l'utilisateur connecté
     */
    public function getNotifications()
    {
        $userId = auth()->id();
        $notifications = NotificationService::getUrgentNotifications($userId);
        $totalCount = count($notifications);
        $urgentCount = NotificationService::getUrgentNotificationsCount($userId);

        return response()->json([
            'notifications' => $notifications,
            'total_count' => $totalCount,
            'urgent_count' => $urgentCount
        ]);
    }

    /**
     * Récupère uniquement le compteur de notifications pour l'utilisateur connecté
     */
    public function getNotificationCount()
    {
        $userId = auth()->id();
        return response()->json([
            'total_count' => NotificationService::getTotalNotificationsCount($userId),
            'urgent_count' => NotificationService::getUrgentNotificationsCount($userId)
        ]);
    }

    /**
     * Marquer une notification comme lue
     */
    public function markAsRead(Request $request)
    {
        $request->validate([
            'type' => 'required|string',
            'notification_id' => 'required|integer'
        ]);

        $userId = auth()->id();

        NotificationService::markAsRead(
            $userId,
            $request->type,
            $request->notification_id
        );

        return response()->json([
            'message' => 'Notification marquée comme lue'
        ]);
    }

    /**
     * Marquer toutes les notifications comme lues
     */
    public function markAllAsRead()
    {
        $userId = auth()->id();
        NotificationService::markAllAsRead($userId);

        return response()->json([
            'message' => 'Toutes les notifications ont été marquées comme lues'
        ]);
    }

    /**
     * Envoie une notification push (pour test)
     */
    public function sendTestNotification(Request $request)
    {
        // Cette méthode pourra être utilisée plus tard pour envoyer des notifications push réelles
        // Pour l'instant, on retourne juste une confirmation

        return response()->json([
            'message' => 'Notification test envoyée',
            'data' => [
                'title' => 'Test Solelec',
                'message' => 'Ceci est une notification de test',
                'priority' => 'medium'
            ]
        ]);
    }
}

