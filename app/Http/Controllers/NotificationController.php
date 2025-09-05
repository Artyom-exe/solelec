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
    public function markAsRead(\App\Http\Requests\NotificationMarkAsReadRequest $request)
    {
        $validated = $request->validated();

        $userId = auth()->id();

        NotificationService::markAsRead(
            $userId,
            $validated['type'],
            $validated['notification_id']
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
}

