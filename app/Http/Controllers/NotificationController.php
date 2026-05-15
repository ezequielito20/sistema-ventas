<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $companyId = Auth::user()->company_id;

        $notifications = Notification::query()
            ->where('user_id', Auth::id())
            ->when($companyId, fn ($q) => $q->where(function ($q2) use ($companyId) {
                $q2->whereNull('company_id')->orWhere('company_id', $companyId);
            }))
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.notifications.index', compact('notifications'));
    }

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

    public function getUnreadCount()
    {
        if (! Auth::user()?->can('orders.index')) {
            return response()->json(['count' => 0]);
        }

        $companyId = Auth::user()->company_id;

        $count = Notification::query()
            ->where('user_id', Auth::id())
            ->where('is_read', false)
            ->where('type', 'new_order')
            ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
            ->pendingOrderAlerts()
            ->count();

        return response()->json(['count' => $count]);
    }

    public function getRecentNotifications()
    {
        if (! Auth::user()?->can('orders.index')) {
            return response()->json(['notifications' => []]);
        }

        $companyId = Auth::user()->company_id;

        $notifications = Notification::query()
            ->where('user_id', Auth::id())
            ->where('type', 'new_order')
            ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
            ->pendingOrderAlerts()
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        $notifications->each(function ($notification) {
            $notification->time_ago = $notification->created_at->diffForHumans();
        });

        return response()->json(['notifications' => $notifications]);
    }

    public function markAllAsRead()
    {
        Notification::query()
            ->where('user_id', Auth::id())
            ->unread()
            ->update(['is_read' => true, 'read_at' => now()]);

        return response()->json(['success' => true]);
    }
}
