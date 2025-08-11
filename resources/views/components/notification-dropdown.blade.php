@php
    $notifications = \App\Models\Notification::where('user_id', auth()->id())
        ->unread()
        ->recent(7)
        ->limit(10)
        ->get();
    
    $unreadCount = \App\Models\Notification::where('user_id', auth()->id())
        ->unread()
        ->count();
@endphp

<li class="nav-item dropdown">
    <a class="nav-link" data-toggle="dropdown" href="#" id="notificationDropdown">
        <i class="fas fa-bell"></i>
        @if($unreadCount > 0)
            <span class="badge badge-warning navbar-badge" id="notificationBadge">{{ $unreadCount }}</span>
        @endif
    </a>
    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right" id="notificationMenu">
        <span class="dropdown-item dropdown-header">
            <i class="fas fa-bell mr-2"></i>
            {{ $unreadCount }} Notificación{{ $unreadCount != 1 ? 'es' : '' }}
        </span>
        
        @if($notifications->count() > 0)
            @foreach($notifications as $notification)
                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-item notification-item" data-id="{{ $notification->id }}" data-order-id="{{ $notification->data['order_id'] ?? '' }}">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="{{ $notification->icon }} text-{{ $notification->color }} mr-2"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="font-weight-bold">{{ $notification->title }}</div>
                            <div class="text-sm text-muted">{{ Str::limit($notification->message, 60) }}</div>
                            <div class="text-xs text-muted mt-1">
                                <i class="far fa-clock mr-1"></i>{{ $notification->time_ago }}
                            </div>
                        </div>
                    </div>
                </a>
            @endforeach
            <div class="dropdown-divider"></div>
            <a href="{{ route('admin.notifications.index') }}" class="dropdown-item dropdown-footer text-center">
                <i class="fas fa-list mr-2"></i>Ver todas las notificaciones
            </a>
        @else
            <div class="dropdown-item text-center text-muted py-4">
                <i class="fas fa-inbox fa-2x mb-2 text-muted"></i><br>
                <span class="text-sm">No hay notificaciones nuevas</span>
            </div>
        @endif
    </div>
</li>

<style>
.notification-item {
    transition: background-color 0.2s ease;
    border-left: 3px solid transparent;
}

.notification-item:hover {
    background-color: #f8f9fa;
    border-left-color: #007bff;
}

.notification-item.new {
    background-color: #fff3cd;
    border-left-color: #ffc107;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.7; }
    100% { opacity: 1; }
}

#notificationBadge {
    animation: bounce 1s infinite;
}

@keyframes bounce {
    0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
    40% { transform: translateY(-3px); }
    60% { transform: translateY(-2px); }
}

.dropdown-header {
    background-color: #f8f9fa;
    font-weight: bold;
    color: #495057;
}

.dropdown-footer {
    background-color: #e9ecef;
    font-weight: bold;
    color: #495057;
}

.dropdown-footer:hover {
    background-color: #dee2e6;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let lastCount = {{ $unreadCount }};
    
    // Function to update notification count
    function updateNotificationCount(count) {
        const badge = document.getElementById('notificationBadge');
        const header = document.querySelector('#notificationMenu .dropdown-header');
        
        if (count > 0) {
            if (badge) {
                badge.textContent = count;
            } else {
                const newBadge = document.createElement('span');
                newBadge.className = 'badge badge-warning navbar-badge';
                newBadge.id = 'notificationBadge';
                newBadge.textContent = count;
                document.getElementById('notificationDropdown').appendChild(newBadge);
            }
            
            // Update header text
            if (header) {
                header.innerHTML = `<i class="fas fa-bell mr-2"></i>${count} Notificación${count != 1 ? 'es' : ''}`;
            }
            
            // Add bounce animation for new notifications
            if (count > lastCount) {
                const newBadge = document.getElementById('notificationBadge');
                if (newBadge) {
                    newBadge.style.animation = 'bounce 1s infinite';
                    setTimeout(() => {
                        newBadge.style.animation = '';
                    }, 3000);
                }
            }
        } else {
            if (badge) {
                badge.remove();
            }
            if (header) {
                header.innerHTML = '<i class="fas fa-bell mr-2"></i>0 Notificaciones';
            }
        }
        
        lastCount = count;
    }
    
    // Function to refresh notifications
    function refreshNotifications() {
        fetch('{{ route("admin.notifications.unread-count") }}')
            .then(response => response.json())
            .then(data => {
                updateNotificationCount(data.count);
            })
            .catch(error => console.log('Error updating notifications:', error));
    }
    
    // Auto-refresh notifications every 10 seconds (more frequent for real-time feel)
    setInterval(refreshNotifications, 10000);
    
    // Also refresh when the page becomes visible
    document.addEventListener('visibilitychange', function() {
        if (!document.hidden) {
            refreshNotifications();
        }
    });
    
    // Handle notification click
    document.addEventListener('click', function(e) {
        if (e.target.closest('.notification-item')) {
            e.preventDefault();
            const item = e.target.closest('.notification-item');
            const notificationId = item.dataset.id;
            const orderId = item.dataset.orderId;
            
            // Mark as read
            fetch(`/admin/notifications/${notificationId}/read`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // If it's an order notification, redirect to order details
                    if (orderId) {
                        window.location.href = `/admin/orders/${orderId}`;
                    } else {
                        // Reload notifications
                        location.reload();
                    }
                }
            })
            .catch(error => console.log('Error marking notification as read:', error));
        }
    });
    
    // Add sound notification for new orders (optional)
    function playNotificationSound() {
        // Create a simple beep sound
        const audioContext = new (window.AudioContext || window.webkitAudioContext)();
        const oscillator = audioContext.createOscillator();
        const gainNode = audioContext.createGain();
        
        oscillator.connect(gainNode);
        gainNode.connect(audioContext.destination);
        
        oscillator.frequency.value = 800;
        gainNode.gain.value = 0.1;
        
        oscillator.start();
        setTimeout(() => {
            oscillator.stop();
        }, 200);
    }
    
    // Check for new notifications every 5 seconds
    setInterval(function() {
        fetch('{{ route("admin.notifications.unread-count") }}')
            .then(response => response.json())
            .then(data => {
                if (data.count > lastCount) {
                    // Play sound for new notifications
                    playNotificationSound();
                }
                updateNotificationCount(data.count);
            })
            .catch(error => console.log('Error checking notifications:', error));
    }, 5000);
});
</script>
