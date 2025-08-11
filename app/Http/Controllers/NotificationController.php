<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Display a listing of notifications.
     */
    public function index()
    {
        $notifications = Notification::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.notifications.index', compact('notifications'));
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead(Notification $notification)
    {
        if ($notification->user_id !== Auth::id()) {
            abort(403);
        }

        $notification->markAsRead();

        if (request()->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', 'Notificación marcada como leída.');
    }

    /**
     * Get unread notifications count.
     */
    public function getUnreadCount()
    {
        $count = Notification::where('user_id', Auth::id())
            ->pendingOrders()
            ->count();

        return response()->json(['count' => $count]);
    }

    /**
     * Get recent unread notifications for dropdown.
     */
    public function getRecentNotifications()
    {
        $notifications = Notification::where('user_id', Auth::id())
            ->pendingOrders()
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Agregar el atributo time_ago a cada notificación
        $notifications->each(function ($notification) {
            $notification->time_ago = $notification->created_at->diffForHumans();
        });

        return response()->json(['notifications' => $notifications]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead()
    {
        Notification::where('user_id', Auth::id())
            ->unread()
            ->update(['is_read' => true, 'read_at' => now()]);

        return response()->json(['success' => true]);
    }
}
