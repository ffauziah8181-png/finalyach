<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserNotification;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $notifikasi = UserNotification::where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->get()
            ->groupBy(function ($n) {
                return $n->created_at->isToday() ? 'Hari Ini'
                    : ($n->created_at->isCurrentWeek() ? 'Minggu Ini' : 'Lebih Lama');
            });

        return $this->success('Berhasil mengambil notifikasi.', $notifikasi);
    }

    public function markAsRead(Request $request, UserNotification $notification)
    {
        $this->authorize('update', $notification);

        $notification->update(['is_read' => true]);

        return $this->success('Ditandai sudah dibaca.');
    }

    public function markAllAsRead(Request $request)
    {
        UserNotification::where('user_id', $request->user()->id)->update(['is_read' => true]);

        return $this->success('Semua notifikasi ditandai sudah dibaca.');
    }

    public function destroy(Request $request, UserNotification $notification)
    {
        $this->authorize('delete', $notification);

        $notification->delete();

        return $this->success('Notifikasi dihapus.');
    }
}
