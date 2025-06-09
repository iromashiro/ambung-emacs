<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    /**
     * Display a listing of the user's notifications
     */
    public function index()
    {
        $user = auth()->user();
        
        // Get unread notifications
        $unreadNotifications = $user->unreadNotifications;
        
        // Get read notifications (paginated)
        $readNotifications = $user->readNotifications()->paginate(10);
        
        return view('web.notifications.index', compact(
            'unreadNotifications',
            'readNotifications'
        ));
    }
    
    /**
     * Mark a notification as read
     */
    public function markAsRead(DatabaseNotification $notification)
    {
        // Check if notification belongs to user
        if ($notification->notifiable_id !== auth()->id()) {
            abort(403);
        }
        
        $notification->markAsRead();
        
        return redirect()->back()->with('success', 'Notification marked as read.');
    }
    
    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        $user = auth()->user();
        $user->unreadNotifications->markAsRead();
        
        return redirect()->back()->with('success', 'All notifications marked as read.');
    }
}