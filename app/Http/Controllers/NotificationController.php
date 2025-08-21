<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Récupère toutes les notifications urgentes
     */
    public function getNotifications()
    {
        $notifications = NotificationService::getUrgentNotifications();
        $totalCount = count($notifications);
        $urgentCount = NotificationService::getUrgentNotificationsCount();

        return response()->json([
            'notifications' => $notifications,
            'total_count' => $totalCount,
            'urgent_count' => $urgentCount
        ]);
    }

    /**
     * Récupère uniquement le compteur de notifications
     */
    public function getNotificationCount()
    {
        return response()->json([
            'total_count' => NotificationService::getTotalNotificationsCount(),
            'urgent_count' => NotificationService::getUrgentNotificationsCount()
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
