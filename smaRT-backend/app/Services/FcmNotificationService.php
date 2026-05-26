<?php

namespace App\Services;

use App\Models\FcmToken;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Illuminate\Support\Facades\Log;

class FcmNotificationService
{
    protected Messaging $messaging;

    public function __construct(Messaging $messaging)
    {
        $this->messaging = $messaging;
    }

    /**
     * Kirim notifikasi ke satu user.
     */
    public function sendToUser(string $userId, string $title, string $body, array $data = []): void
    {
        $tokens = FcmToken::where('user_id', $userId)->pluck('token')->toArray();

        if (empty($tokens)) {
            Log::info("FCM: User {$userId} tidak punya token terdaftar.");
            return;
        }

        $this->sendToTokens($tokens, $title, $body, $data);
    }

    /**
     * Kirim notifikasi ke banyak user sekaligus.
     */
    public function sendToUsers(array $userIds, string $title, string $body, array $data = []): void
    {
        $tokens = FcmToken::whereIn('user_id', $userIds)->pluck('token')->toArray();

        if (empty($tokens)) {
            return;
        }

        $this->sendToTokens($tokens, $title, $body, $data);
    }

    /**
     * Kirim notifikasi ke semua user di RT tertentu.
     */
    public function sendToRT(string $rtId, string $title, string $body, array $data = []): void
    {
        $tokens = FcmToken::whereHas('user', function ($query) use ($rtId) {
            $query->where('id_rt', $rtId);
        })->pluck('token')->toArray();

        if (empty($tokens)) {
            return;
        }

        $this->sendToTokens($tokens, $title, $body, $data);
    }

    /**
     * Kirim ke list token FCM.
     * Mendukung batch per 500 token sesuai batas FCM.
     */
    private function sendToTokens(array $tokens, string $title, string $body, array $data = []): void
    {
        $notification = Notification::create($title, $body);

        // Tambahkan title dan body ke data payload juga
        // agar bisa ditangkap oleh onMessageReceived() di Android
        $data = array_merge($data, [
            'title' => $title,
            'body'  => $body,
        ]);

        $message = CloudMessage::new()
            ->withNotification($notification)
            ->withData($data);

        // FCM membatasi max 500 token per batch multicast
        $batches = array_chunk($tokens, 500);

        foreach ($batches as $batch) {
            try {
                $report = $this->messaging->sendMulticast($message, $batch);

                Log::info("FCM: {$report->successes()->count()} sukses, {$report->failures()->count()} gagal");

                // Hapus token yang sudah tidak valid
                foreach ($report->failures()->getItems() as $failure) {
                    $invalidToken = $failure->target()->value();
                    FcmToken::where('token', $invalidToken)->delete();
                    Log::warning("FCM: Token tidak valid dihapus: {$invalidToken}");
                }
            } catch (\Exception $e) {
                Log::error("FCM Error: " . $e->getMessage());
            }
        }
    }
}
