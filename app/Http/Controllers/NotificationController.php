<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\User;
use App\Models\Member;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use App\Services\NotificationService;
use App\Services\SmsService;
use App\Services\EmailService;

class NotificationController extends Controller
{
    protected $notificationService;
    protected $smsService;
    protected $emailService;

    public function __construct(
        NotificationService $notificationService,
        SmsService $smsService,
        EmailService $emailService
    ) {
        $this->notificationService = $notificationService;
        $this->smsService = $smsService;
        $this->emailService = $emailService;
    }

    /**
     * Display a listing of notifications for the authenticated user
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $query = Notification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc');

        // Filter by type if provided
        if ($request->has('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        // Filter by read status if provided
        if ($request->has('status')) {
            if ($request->status === 'unread') {
                $query->where('is_read', false);
            } elseif ($request->status === 'read') {
                $query->where('is_read', true);
            }
        }

        // Filter by channel if provided
        if ($request->has('channel') && $request->channel !== 'all') {
            $query->where('channel', $request->channel);
        }

        $notifications = $query->paginate(20);

        // Get counts for different categories
        $stats = [
            'total' => Notification::where('user_id', $user->id)->count(),
            'unread' => Notification::where('user_id', $user->id)->where('is_read', false)->count(),
            'by_type' => Notification::where('user_id', $user->id)
                ->groupBy('type')
                ->selectRaw('type, count(*) as count')
                ->pluck('count', 'type'),
            'by_channel' => Notification::where('user_id', $user->id)
                ->groupBy('channel')
                ->selectRaw('channel, count(*) as count')
                ->pluck('count', 'channel'),
        ];

        return Inertia::render('Notifications/Index', [
            'notifications' => $notifications,
            'stats' => $stats,
            'filters' => $request->only(['type', 'status', 'channel']),
        ]);
    }

    /**
     * Display the specified notification
     */
    public function show(Notification $notification)
    {
        // Ensure user can only view their own notifications
        if ($notification->user_id !== Auth::id()) {
            abort(403, 'Unauthorized to view this notification');
        }

        // Mark as read if not already read
        if (!$notification->is_read) {
            $notification->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        }

        return Inertia::render('Notifications/Show', [
            'notification' => $notification,
        ]);
    }

    /**
     * Mark a notification as read
     */
    public function markAsRead(Notification $notification)
    {
        // Ensure user can only mark their own notifications as read
        if ($notification->user_id !== Auth::id()) {
            abort(403, 'Unauthorized to modify this notification');
        }

        $notification->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read',
        ]);
    }

    /**
     * Mark all notifications as read for the authenticated user
     */
    public function markAllAsRead()
    {
        $user = Auth::user();
        
        Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read',
        ]);
    }

    /**
     * Remove the specified notification
     */
    public function destroy(Notification $notification)
    {
        // Ensure user can only delete their own notifications
        if ($notification->user_id !== Auth::id()) {
            abort(403, 'Unauthorized to delete this notification');
        }

        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notification deleted successfully',
        ]);
    }

    /**
     * Display notification settings
     */
    public function settings()
    {
        $user = Auth::user();
        
        // Get current notification preferences
        $preferences = $user->notification_preferences ?? [
            'email' => [
                'transaction' => true,
                'loan' => true,
                'dividend' => true,
                'general' => true,
                'system' => false,
            ],
            'sms' => [
                'transaction' => true,
                'loan' => true,
                'dividend' => false,
                'general' => false,
                'system' => false,
            ],
            'system' => [
                'transaction' => true,
                'loan' => true,
                'dividend' => true,
                'general' => true,
                'system' => true,
            ],
        ];

        // Get system notification settings
        $systemSettings = SystemSetting::whereIn('key', [
            'notifications.sms.enabled',
            'notifications.email.enabled',
            'notifications.system.enabled',
            'notifications.default.channels',
        ])->pluck('value', 'key');

        return Inertia::render('Notifications/Settings', [
            'preferences' => $preferences,
            'systemSettings' => $systemSettings,
        ]);
    }

    /**
     * Update notification settings
     */
    public function updateSettings(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'preferences' => 'required|array',
            'preferences.email' => 'required|array',
            'preferences.sms' => 'required|array',
            'preferences.system' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = Auth::user();
        $user->update([
            'notification_preferences' => $request->preferences,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Notification settings updated successfully',
        ]);
    }

    /**
     * Show form for creating bulk notifications
     */
    public function createBulk()
    {
        // Only admin and management can send bulk notifications
        if (!in_array(Auth::user()->role, ['admin', 'management'])) {
            abort(403, 'Unauthorized to send bulk notifications');
        }

        // Get available recipient groups
        $recipientGroups = [
            'all_members' => 'All Members',
            'active_members' => 'Active Members',
            'inactive_members' => 'Inactive Members',
            'loan_defaulters' => 'Loan Defaulters',
            'savings_members' => 'Members with Savings',
            'share_holders' => 'Share Holders',
            'loan_officers' => 'Loan Officers',
            'accountants' => 'Accountants',
            'management' => 'Management',
            'custom' => 'Custom Selection',
        ];

        return Inertia::render('Notifications/BulkCreate', [
            'recipientGroups' => $recipientGroups,
        ]);
    }

    /**
     * Send bulk notifications
     */
    public function sendBulk(Request $request)
    {
        // Only admin and management can send bulk notifications
        if (!in_array(Auth::user()->role, ['admin', 'management'])) {
            abort(403, 'Unauthorized to send bulk notifications');
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:1000',
            'type' => ['required', Rule::in(['transaction', 'loan', 'dividend', 'general', 'system'])],
            'channels' => 'required|array|min:1',
            'channels.*' => Rule::in(['sms', 'email', 'system']),
            'recipient_group' => 'required|string',
            'custom_recipients' => 'nullable|array',
            'custom_recipients.*' => 'exists:users,id',
            'schedule_at' => 'nullable|date|after:now',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Get recipients based on selection
            $recipients = $this->getRecipients($request->recipient_group, $request->custom_recipients);

            if ($recipients->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No recipients found for the selected group',
                ], 400);
            }

            $sendCount = 0;
            $failedCount = 0;

            foreach ($recipients as $recipient) {
                try {
                    // Send notification through selected channels
                    foreach ($request->channels as $channel) {
                        $notification = Notification::create([
                            'user_id' => $recipient->id,
                            'title' => $request->title,
                            'message' => $request->message,
                            'type' => $request->type,
                            'channel' => $channel,
                            'status' => 'pending',
                            'metadata' => [
                                'bulk_notification' => true,
                                'sent_by' => Auth::id(),
                                'scheduled_at' => $request->schedule_at,
                            ],
                        ]);

                        // Send immediately if not scheduled
                        if (!$request->schedule_at) {
                            $this->sendNotification($notification, $recipient);
                        }

                        $sendCount++;
                    }
                } catch (\Exception $e) {
                    $failedCount++;
                    Log::error('Failed to send bulk notification to user ' . $recipient->id . ': ' . $e->getMessage());
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Bulk notification sent successfully. {$sendCount} notifications sent, {$failedCount} failed.",
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk notification failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to send bulk notification: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get recipients based on group selection
     */
    protected function getRecipients($group, $customRecipients = null)
    {
        switch ($group) {
            case 'all_members':
                return User::whereHas('member')->get();
            
            case 'active_members':
                return User::whereHas('member', function ($query) {
                    $query->where('membership_status', 'active');
                })->get();
            
            case 'inactive_members':
                return User::whereHas('member', function ($query) {
                    $query->where('membership_status', 'inactive');
                })->get();
            
            case 'loan_defaulters':
                return User::whereHas('member.loans', function ($query) {
                    $query->where('days_in_arrears', '>', 0);
                })->get();
            
            case 'savings_members':
                return User::whereHas('member.accounts', function ($query) {
                    $query->where('account_type', 'savings')
                          ->where('balance', '>', 0);
                })->get();
            
            case 'share_holders':
                return User::whereHas('member.accounts', function ($query) {
                    $query->where('account_type', 'shares')
                          ->where('balance', '>', 0);
                })->get();
            
            case 'loan_officers':
                return User::where('role', 'loan_officer')->get();
            
            case 'accountants':
                return User::where('role', 'accountant')->get();
            
            case 'management':
                return User::whereIn('role', ['management', 'admin'])->get();
            
            case 'custom':
                return User::whereIn('id', $customRecipients ?? [])->get();
            
            default:
                return collect();
        }
    }

    /**
     * Send individual notification
     */
    protected function sendNotification($notification, $recipient)
    {
        try {
            switch ($notification->channel) {
                case 'sms':
                    if ($recipient->phone) {
                        $this->smsService->send($recipient->phone, $notification->message);
                        $notification->update(['status' => 'sent', 'sent_at' => now()]);
                    }
                    break;
                
                case 'email':
                    if ($recipient->email) {
                        $this->emailService->send($recipient->email, $notification->title, $notification->message);
                        $notification->update(['status' => 'sent', 'sent_at' => now()]);
                    }
                    break;
                
                case 'system':
                    $notification->update(['status' => 'sent', 'sent_at' => now()]);
                    break;
            }
        } catch (\Exception $e) {
            $notification->update(['status' => 'failed']);
            throw $e;
        }
    }

    /**
     * Get notification statistics for dashboard
     */
    public function getStats()
    {
        $user = Auth::user();
        
        return [
            'unread_count' => Notification::where('user_id', $user->id)
                ->where('is_read', false)
                ->count(),
            'today_count' => Notification::where('user_id', $user->id)
                ->whereDate('created_at', today())
                ->count(),
            'recent_notifications' => Notification::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get(),
        ];
    }

    /**
     * Get unread notifications for real-time updates
     */
    public function getUnread()
    {
        $user = Auth::user();
        
        $notifications = Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'notifications' => $notifications,
            'count' => $notifications->count(),
        ]);
    }

    /**
     * Send test notification
     */
    public function sendTest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'channel' => ['required', Rule::in(['sms', 'email', 'system'])],
            'message' => 'required|string|max:160',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = Auth::user();

        try {
            $notification = Notification::create([
                'user_id' => $user->id,
                'title' => 'Test Notification',
                'message' => $request->message,
                'type' => 'system',
                'channel' => $request->channel,
                'status' => 'pending',
            ]);

            $this->sendNotification($notification, $user);

            return response()->json([
                'success' => true,
                'message' => 'Test notification sent successfully',
            ]);

        } catch (\Exception $e) {
            Log::error('Test notification failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to send test notification: ' . $e->getMessage(),
            ], 500);
        }
    }
}