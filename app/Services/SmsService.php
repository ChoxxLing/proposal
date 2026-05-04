<?php

class SmsService
{
    public function send(string $phone, string $message): array
    {
        $apiUrl = getenv('SMS_API_URL') ?: '';
        $apiKey = getenv('SMS_API_KEY') ?: '';

        if ($apiUrl === '' || $apiKey === '') {
            return [
                'status' => 'simulated',
                'provider_response' => 'No SMS gateway configured. Message logged only.',
            ];
        }

        $payload = http_build_query([
            'api_key' => $apiKey,
            'phone' => $phone,
            'message' => $message,
        ]);

        $ch = curl_init($apiUrl);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
        ]);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        $statusCode = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);

        if ($response === false || $statusCode >= 400) {
            return [
                'status' => 'failed',
                'provider_response' => $error ?: (string) $response,
            ];
        }

        return [
            'status' => 'sent',
            'provider_response' => (string) $response,
        ];
    }
}
