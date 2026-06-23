<?php
namespace App\Services;

use App\Models\Order;
use RuntimeException;

class MidtransSnapService
{
    private array $config;

    public function __construct()
    {
        $this->config = config('services.midtrans', []);
    }

    public function isProduction(): bool
    {
        return filter_var($this->config['is_production'] ?? false, FILTER_VALIDATE_BOOLEAN);
    }

    public function isConfigured(): bool
    {
        return filled($this->serverKey()) && filled($this->clientKey());
    }

    public function clientKey(): string
    {
        return (string) ($this->config['client_key'] ?? '');
    }

    private function serverKey(): string
    {
        return (string) ($this->config['server_key'] ?? '');
    }

    public function expiryMinutes(): int
    {
        return max(1, (int) ($this->config['payment_expiry_minutes'] ?? env('MIDTRANS_PAYMENT_EXPIRY_MINUTES', 6)));
    }

    private function sslVerify(): bool
    {
        return filter_var($this->config['ssl_verify'] ?? env('MIDTRANS_SSL_VERIFY', true), FILTER_VALIDATE_BOOLEAN);
    }

    private function caInfoPath(): ?string
    {
        $configured = (string) ($this->config['ca_info'] ?? env('MIDTRANS_CAINFO', ''));
        $candidates = array_filter([
            $configured,
            base_path('storage/certs/cacert.pem'),
            base_path('cacert.pem'),
        ]);

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && $candidate !== '' && is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    public function snapJsUrl(): string
    {
        return $this->isProduction()
            ? 'https://app.midtrans.com/snap/snap.js'
            : 'https://app.sandbox.midtrans.com/snap/snap.js';
    }

    private function snapApiUrl(): string
    {
        return $this->isProduction()
            ? 'https://app.midtrans.com/snap/v1/transactions'
            : 'https://app.sandbox.midtrans.com/snap/v1/transactions';
    }

    private function statusApiUrl(string $orderId): string
    {
        $encoded = rawurlencode($orderId);
        return $this->isProduction()
            ? "https://api.midtrans.com/v2/{$encoded}/status"
            : "https://api.sandbox.midtrans.com/v2/{$encoded}/status";
    }

    public function officialSnapPayments(): array
    {
        // Ini yang membuat popup resmi Midtrans seperti contoh: Transfer Bank, Card, GoPay/QRIS, ShopeePay, retail.
        return [
            'bca_va',
            'bni_va',
            'bri_va',
            'permata_va',
            'echannel',
            'other_va',
            'credit_card',
            'gopay',
            'qris',
            'shopeepay',
            'alfamart',
            'indomaret',
        ];
    }

    public function createSnapTransaction(Order $order): array
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('MIDTRANS_SERVER_KEY dan MIDTRANS_CLIENT_KEY belum diisi di file .env.');
        }

        $payload = $this->baseTransactionPayload($order->loadMissing(['items', 'user']));
        $payload['enabled_payments'] = $this->officialSnapPayments();

        return $this->request('POST', $this->snapApiUrl(), $payload);
    }

    private function baseTransactionPayload(Order $order): array
    {
        $order->loadMissing(['items', 'user']);
        $items = [];

        foreach ($order->items as $item) {
            $items[] = [
                'id' => (string) ($item->product_id ?: $item->id),
                'price' => (int) $item->price,
                'quantity' => (int) $item->quantity,
                'name' => mb_substr((string) $item->product_name, 0, 50),
            ];
        }

        if ((int) $order->shipping_cost > 0) {
            $shipping = is_array($order->shipping_snapshot) ? $order->shipping_snapshot : [];
            $items[] = [
                'id' => 'shipping',
                'price' => (int) $order->shipping_cost,
                'quantity' => 1,
                'name' => mb_substr('Ongkir ' . ($shipping['courier_label'] ?? 'SobatAnak'), 0, 50),
            ];
        }

        $shipping = is_array($order->shipping_snapshot) ? $order->shipping_snapshot : [];
        $name = (string) ($shipping['recipient_name'] ?? $order->user->name ?? 'Pelanggan SobatAnak');
        $phone = (string) ($shipping['phone'] ?? '');
        $email = (string) ($order->user->email ?? 'customer@sobatanak.local');

        return [
            'transaction_details' => [
                'order_id' => $order->order_number,
                'gross_amount' => (int) $order->total_amount,
            ],
            'item_details' => $items,
            'customer_details' => [
                'first_name' => mb_substr($name, 0, 255),
                'email' => $email,
                'phone' => $phone,
                'billing_address' => [
                    'first_name' => mb_substr($name, 0, 255),
                    'phone' => $phone,
                    'address' => (string) ($shipping['address'] ?? ''),
                    'city' => (string) ($shipping['city'] ?? ''),
                    'postal_code' => (string) ($shipping['postal_code'] ?? ''),
                    'country_code' => 'IDN',
                ],
                'shipping_address' => [
                    'first_name' => mb_substr($name, 0, 255),
                    'phone' => $phone,
                    'address' => (string) ($shipping['address'] ?? ''),
                    'city' => (string) ($shipping['city'] ?? ''),
                    'postal_code' => (string) ($shipping['postal_code'] ?? ''),
                    'country_code' => 'IDN',
                ],
            ],
            'callbacks' => [
                'finish' => route('checkout.finish', $order),
            ],
            'expiry' => [
                'unit' => 'minutes',
                'duration' => $this->expiryMinutes(),
            ],
        ];
    }

    public function paymentDetailFromPayload(array $payload): array
    {
        $detail = [
            'payment_type' => $payload['payment_type'] ?? null,
            'transaction_status' => $payload['transaction_status'] ?? null,
            'transaction_time' => $payload['transaction_time'] ?? null,
            'expiry_time' => $payload['expiry_time'] ?? null,
        ];

        if (! empty($payload['va_numbers']) && is_array($payload['va_numbers'])) {
            $firstVa = $payload['va_numbers'][0] ?? [];
            $detail['va_numbers'] = $payload['va_numbers'];
            $detail['bank'] = $firstVa['bank'] ?? null;
            $detail['va_number'] = $firstVa['va_number'] ?? null;
            $detail['payment_code'] = $firstVa['va_number'] ?? null;
        }

        if (! empty($payload['permata_va_number'])) {
            $detail['bank'] = 'permata';
            $detail['va_number'] = $payload['permata_va_number'];
            $detail['payment_code'] = $payload['permata_va_number'];
        }

        foreach (['biller_code', 'bill_key', 'payment_code', 'store', 'acquirer', 'pdf_url'] as $key) {
            if (! empty($payload[$key])) {
                $detail[$key] = $payload[$key];
            }
        }

        if (! empty($payload['issuer'])) {
            $detail['issuer'] = $payload['issuer'];
        }

        if (! empty($payload['actions']) && is_array($payload['actions'])) {
            $detail['actions'] = $payload['actions'];
        }

        return array_filter($detail, fn($value) => $value !== null && $value !== '');
    }

    public function checkStatus(string $orderNumber): array
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('MIDTRANS_SERVER_KEY dan MIDTRANS_CLIENT_KEY belum diisi di file .env.');
        }

        return $this->request('GET', $this->statusApiUrl($orderNumber));
    }

    public function verifySignature(array $payload): bool
    {
        $signature = (string) ($payload['signature_key'] ?? '');
        if ($signature === '' || $this->serverKey() === '') {
            return false;
        }

        $raw = (string) ($payload['order_id'] ?? '')
            . (string) ($payload['status_code'] ?? '')
            . (string) ($payload['gross_amount'] ?? '')
            . $this->serverKey();

        return hash_equals(hash('sha512', $raw), $signature);
    }

    public function mapOrderStatus(?string $transactionStatus, ?string $fraudStatus = null): string
    {
        $transactionStatus = strtolower((string) $transactionStatus);
        $fraudStatus = strtolower((string) $fraudStatus);

        return match ($transactionStatus) {
            'settlement' => 'paid',
            'capture' => in_array($fraudStatus, ['', 'accept'], true) ? 'paid' : 'challenge',
            'pending' => 'pending',
            'deny', 'failure' => 'failed',
            'cancel' => 'cancelled',
            'expire' => 'expired',
            'refund', 'partial_refund' => 'refund',
            default => 'pending',
        };
    }

    private function request(string $method, string $url, ?array $payload = null): array
    {
        $headers = [
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: Basic ' . base64_encode($this->serverKey() . ':'),
        ];

        $body = $payload !== null ? json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : null;

        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            $curlOptions = [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CUSTOMREQUEST => $method,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_SSL_VERIFYHOST => $this->sslVerify() ? 2 : 0,
                CURLOPT_SSL_VERIFYPEER => $this->sslVerify(),
            ];

            if ($this->sslVerify()) {
                $caInfo = $this->caInfoPath();
                if ($caInfo) {
                    $curlOptions[CURLOPT_CAINFO] = $caInfo;
                }
            }

            curl_setopt_array($ch, $curlOptions);
            if ($body !== null) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
            }
            $response = curl_exec($ch);
            $error = curl_error($ch);
            $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($response === false) {
                $message = 'Gagal menghubungi Midtrans: ' . $error;
                if (str_contains(strtolower($error), 'certificate') || str_contains(strtolower($error), 'ssl')) {
                    $message .= '. File sertifikat CA sudah disediakan di storage/certs/cacert.pem. Jalankan php artisan config:clear lalu coba lagi.';
                }
                throw new RuntimeException($message);
            }
        } else {
            $sslOptions = [
                'verify_peer' => $this->sslVerify(),
                'verify_peer_name' => $this->sslVerify(),
            ];

            if ($this->sslVerify()) {
                $caInfo = $this->caInfoPath();
                if ($caInfo) {
                    $sslOptions['cafile'] = $caInfo;
                }
            }

            $context = stream_context_create([
                'http' => [
                    'method' => $method,
                    'header' => implode("\r\n", $headers),
                    'content' => $body ?? '',
                    'timeout' => 30,
                    'ignore_errors' => true,
                ],
                'ssl' => $sslOptions,
            ]);
            $response = file_get_contents($url, false, $context);
            $status = 0;
            if (isset($http_response_header[0]) && preg_match('/\s(\d{3})\s/', $http_response_header[0], $m)) {
                $status = (int) $m[1];
            }
            if ($response === false) {
                throw new RuntimeException('Gagal menghubungi Midtrans. Pastikan internet server aktif.');
            }
        }

        $json = json_decode((string) $response, true);
        if (! is_array($json)) {
            throw new RuntimeException('Response Midtrans tidak valid: ' . mb_substr((string) $response, 0, 250));
        }

        if ($status >= 400) {
            $message = $json['error_messages'][0] ?? $json['status_message'] ?? $json['message'] ?? 'Request Midtrans gagal.';
            throw new RuntimeException($message);
        }

        return $json;
    }
}
