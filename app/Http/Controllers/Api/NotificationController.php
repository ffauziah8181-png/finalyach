<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifikasi = UserNotification::where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->get()
            ->groupBy(function ($n) {
                return $n->created_at->isToday() ? 'Hari Ini'
                    : ($n->created_at->isCurrentWeek() ? 'Minggu Ini' : 'Lebih Lama');
            });

        return response()->json($notifikasi);
    }

    public function markAsRead(Request $request, UserNotification $notification)
    {
        abort_if($notification->user_id !== $request->user()->id, 403);
        $notification->update(['is_read' => true]);

        return response()->json(['message' => 'Ditandai sudah dibaca.']);
    }

    public function markAllAsRead(Request $request)
    {
        UserNotification::where('user_id', $request->user()->id)->update(['is_read' => true]);

        return response()->json(['message' => 'Semua notifikasi ditandai sudah dibaca.']);
    }

    public function destroy(Request $request, UserNotification $notification)
    {
        abort_if($notification->user_id !== $request->user()->id, 403);
        $notification->delete();

        return response()->json(['message' => 'Notifikasi dihapus.']);
    }
}
