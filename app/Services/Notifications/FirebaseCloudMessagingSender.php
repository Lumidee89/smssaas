<?php

namespace App\Services\Notifications;

use App\Contracts\PushSender;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class FirebaseCloudMessagingSender implements PushSender
{
    public function send(string $token, string $title, string $body, array $data = []): bool
    {
        $projectId = config('services.firebase.project_id');
        if (! $projectId) {
            throw new RuntimeException('Firebase project ID is not configured.');
        }

        $response = Http::withToken($this->accessToken())->post(
            "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send",
            ['message' => [
                'token' => $token,
                'notification' => ['title' => $title, 'body' => $body],
                'data' => collect($data)->mapWithKeys(fn ($value, $key) => [(string) $key => (string) $value])->all(),
                'android' => ['priority' => 'high'],
                'apns' => ['payload' => ['aps' => ['sound' => 'default']]],
            ]],
        );

        if ($response->successful()) {
            return true;
        }
        $status = data_get($response->json(), 'error.status');
        if (in_array($status, ['UNREGISTERED', 'INVALID_ARGUMENT'], true)) {
            return false;
        }
        $response->throw();

        return true;
    }

    private function accessToken(): string
    {
        return Cache::remember('firebase.messaging.access_token', now()->addMinutes(50), function (): string {
            $email = config('services.firebase.client_email');
            $privateKey = str_replace('\\n', "\n", (string) config('services.firebase.private_key'));
            if (! $email || ! $privateKey) {
                throw new RuntimeException('Firebase service account is not configured.');
            }

            $now = time();
            $header = $this->base64Url(json_encode(['alg' => 'RS256', 'typ' => 'JWT'], JSON_THROW_ON_ERROR));
            $claims = $this->base64Url(json_encode([
                'iss' => $email,
                'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                'aud' => 'https://oauth2.googleapis.com/token',
                'iat' => $now,
                'exp' => $now + 3600,
            ], JSON_THROW_ON_ERROR));
            $unsigned = $header.'.'.$claims;
            if (! openssl_sign($unsigned, $signature, $privateKey, OPENSSL_ALGO_SHA256)) {
                throw new RuntimeException('Unable to sign Firebase service-account assertion.');
            }

            return Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $unsigned.'.'.$this->base64Url($signature),
            ])->throw()->json('access_token');
        });
    }

    private function base64Url(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
