<?php
namespace App\Services;

use RuntimeException;
use Throwable;

class RajaOngkirService
{
    private array $config;

    public function __construct()
    {
        $this->config = config('services.rajaongkir', []);
    }

    public function isConfigured(): bool
    {
        return filled($this->apiKey()) && filled($this->originId());
    }

    private function apiKey(): string
    {
        // Fallback ke env() supaya tetap kebaca walaupun config/services.php lama belum ter-merge.
        return trim((string) ($this->config['api_key'] ?? env('RAJAONGKIR_API_KEY', '')));
    }

    private function baseUrl(): string
    {
        return rtrim((string) ($this->config['base_url'] ?? env('RAJAONGKIR_BASE_URL', 'https://rajaongkir.komerce.id/api/v1')), '/');
    }

    public function originId(): string
    {
        return trim((string) ($this->config['origin_id'] ?? env('RAJAONGKIR_ORIGIN_ID', '')));
    }

    public function defaultWeight(): int
    {
        return max(1, (int) ($this->config['default_weight'] ?? env('RAJAONGKIR_DEFAULT_WEIGHT', 1000)));
    }

    public function couriers(): string
    {
        return trim((string) ($this->config['couriers'] ?? env('RAJAONGKIR_COURIERS', 'jne:sicepat:jnt:tiki:pos')));
    }

    private function sslVerify(): bool
    {
        $value = $this->config['ssl_verify'] ?? env('RAJAONGKIR_SSL_VERIFY', env('MIDTRANS_SSL_VERIFY', true));
        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? true;
    }

    private function caInfo(): ?string
    {
        $path = (string) ($this->config['ca_info'] ?? env('RAJAONGKIR_CAINFO', env('MIDTRANS_CAINFO', 'storage/certs/cacert.pem')));
        if ($path === '') return null;

        $full = str_starts_with($path, DIRECTORY_SEPARATOR) || preg_match('/^[A-Z]:\\\\/i', $path)
            ? $path
            : base_path($path);

        return is_file($full) ? $full : null;
    }

    public function searchDomesticDestinations(string $keyword, int $limit = 10): array
    {
        $keyword = trim($keyword);
        if (! $this->apiKey() || $keyword === '') {
            return [];
        }

        $query = http_build_query([
            'search' => $keyword,
            'limit' => max(1, min(50, $limit)),
            'offset' => 0,
        ]);

        $response = $this->request('GET', $this->baseUrl() . '/destination/domestic-destination?' . $query);
        return (array) ($response['data'] ?? []);
    }

    public function resolveDestinationId(array $payload): ?array
    {
        $given = trim((string) ($payload['destination_id'] ?? ''));
        if ($given !== '') {
            return ['id' => $given, 'label' => 'Destination ID yang tersimpan', 'source' => 'manual'];
        }

        if (! $this->apiKey()) {
            return null;
        }

        $district = trim((string) ($payload['district_name'] ?? ''));
        $city = trim((string) ($payload['city'] ?? ''));
        $province = trim((string) ($payload['province'] ?? ''));
        $postal = trim((string) ($payload['postal_code'] ?? ''));
        $address = trim((string) ($payload['address'] ?? ''));

        // Prioritas dibuat lebih aman: kode pos + kota dulu, lalu alamat.
        // Ini mencegah salah pilih kalau user salah isi kecamatan.
        $keywords = array_values(array_unique(array_filter([
            trim($postal . ' ' . $city),
            trim($postal . ' ' . $district),
            trim($postal),
            trim($district . ' ' . $city),
            trim($address . ' ' . $postal),
            trim($address . ' ' . $city),
            trim($city . ' ' . $province),
        ])));

        foreach ($keywords as $keyword) {
            $rows = $this->searchDomesticDestinations($keyword, 30);
            $best = $this->pickBestDestination($rows, $district, $city, $province, $postal, $address);
            if ($best && isset($best['id'])) {
                return [
                    'id' => (string) $best['id'],
                    'label' => (string) ($best['label'] ?? $best['subdistrict_name'] ?? $keyword),
                    'source' => 'auto',
                    'keyword' => $keyword,
                    'raw' => $best,
                ];
            }
        }

        return null;
    }

    private function pickBestDestination(array $rows, string $district, string $city, string $province, string $postal, string $address = ''): ?array
    {
        if (! $rows) return null;

        $norm = fn($v) => strtoupper(trim((string) $v));
        $district = $norm($district);
        $city = $norm($city);
        $province = $norm($province);
        $postal = trim($postal);
        $address = $norm($address);

        usort($rows, function ($a, $b) use ($district, $city, $province, $postal, $address, $norm) {
            $score = function ($row) use ($district, $city, $province, $postal, $address, $norm) {
                $label = $norm($row['label'] ?? '');
                $rowDistrict = $norm($row['district_name'] ?? '');
                $rowCity = $norm($row['city_name'] ?? '');
                $rowProvince = $norm($row['province_name'] ?? '');
                $rowSubdistrict = $norm($row['subdistrict_name'] ?? '');
                $rowPostal = trim((string) ($row['zip_code'] ?? ''));
                $score = 0;

                if ($postal !== '' && $rowPostal === $postal) $score += 100;
                if ($district !== '' && ($rowDistrict === $district || $rowSubdistrict === $district || str_contains($label, $district))) $score += 35;
                if ($city !== '' && (str_contains($rowCity, $city) || str_contains($label, $city))) $score += 25;
                if ($province !== '' && (str_contains($rowProvince, $province) || str_contains($label, $province))) $score += 15;

                foreach (preg_split('/\s+/', $address) ?: [] as $word) {
                    if (strlen($word) >= 5 && str_contains($label, $word)) $score += 8;
                }

                return $score;
            };
            return $score($b) <=> $score($a);
        });

        return $rows[0] ?? null;
    }

    public function fallbackOptions(int $subtotal = 0, int $weight = 1000): array
    {
        // Dipakai hanya saat API RajaOngkir limit/error. Supaya user tetap bisa memilih ongkir
        // dengan tampilan ramah, tanpa mengubah database dan tanpa memblokir checkout.
        $kg = max(1, (int) ceil(max(1, $weight) / 1000));
        $discount = $subtotal >= 250000 ? 3000 : 0;

        return [
            $this->makeOption('jne', 'JNE', 'REG', 'Reguler', '2-4', max(9000, (12000 * $kg) - $discount)),
            $this->makeOption('jnt', 'J&T Express', 'EZ', 'Reguler', '2-4', max(9000, (13000 * $kg) - $discount)),
            $this->makeOption('sicepat', 'SiCepat', 'REG', 'Reguler', '2-4', max(9000, (11500 * $kg) - $discount)),
            $this->makeOption('pos', 'POS Indonesia', 'POS REGULER', 'Reguler', '3-5', max(8000, (10500 * $kg) - $discount)),
            $this->makeOption('tiki', 'TIKI', 'REG', 'Reguler', '2-5', max(9000, (12500 * $kg) - $discount)),
        ];
    }

    public function calculateDomestic(string $destinationId, int $weight, ?string $couriers = null): array
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('RajaOngkir belum dikonfigurasi. Isi RAJAONGKIR_API_KEY dan RAJAONGKIR_ORIGIN_ID.');
        }

        if (! filled($destinationId)) {
            throw new RuntimeException('Destination ID RajaOngkir belum ditemukan. Isi kota, provinsi, kode pos, dan kecamatan tujuan dengan benar.');
        }

        $payload = http_build_query([
            'origin' => $this->originId(),
            'destination' => $destinationId,
            'weight' => max(1, $weight),
            'courier' => $couriers ?: $this->couriers(),
            'price' => 'lowest',
        ]);

        // Karena kita memakai Search Domestic Destination, endpoint yang benar adalah direct-search:
        // /calculate/domestic-cost. Endpoint /calculate/district/domestic-cost hanya untuk step-by-step district ID.
        try {
            $response = $this->request('POST', $this->baseUrl() . '/calculate/domestic-cost', $payload);
            return $this->normalizeCostResponse($response);
        } catch (Throwable $directError) {
            // Fallback untuk beberapa akun/dataset yang masih menerima district endpoint.
            $response = $this->request('POST', $this->baseUrl() . '/calculate/district/domestic-cost', $payload);
            return $this->normalizeCostResponse($response);
        }
    }

    public function findOption(array $options, ?string $selectedKey): ?array
    {
        $selectedKey = (string) $selectedKey;
        foreach ($options as $option) {
            if (($option['key'] ?? '') === $selectedKey) {
                return $option;
            }
        }
        return $options[0] ?? null;
    }

    public function normalizeCostResponse(array $response): array
    {
        $rows = $response['data'] ?? $response['rajaongkir']['results'] ?? [];
        $options = [];

        foreach ((array) $rows as $row) {
            if (isset($row['cost']) || isset($row['service'])) {
                $cost = $this->extractCost($row);
                $courier = strtolower((string) ($row['code'] ?? $row['courier'] ?? $row['name'] ?? 'kurir'));
                $service = (string) ($row['service'] ?? $row['type'] ?? 'REG');
                $options[] = $this->makeOption($courier, $row['name'] ?? strtoupper($courier), $service, $row['description'] ?? '', $row['etd'] ?? $row['duration'] ?? '', $cost);
                continue;
            }

            $courier = strtolower((string) ($row['code'] ?? $row['name'] ?? 'kurir'));
            $label = (string) ($row['name'] ?? strtoupper($courier));
            foreach ((array) ($row['costs'] ?? []) as $costRow) {
                $cost = $this->extractCost($costRow);
                $options[] = $this->makeOption($courier, $label, (string) ($costRow['service'] ?? 'REG'), (string) ($costRow['description'] ?? ''), (string) ($costRow['cost'][0]['etd'] ?? $costRow['etd'] ?? ''), $cost);
            }
        }

        usort($options, fn($a, $b) => ($a['cost'] ?? 0) <=> ($b['cost'] ?? 0));
        return $options;
    }

    private function makeOption(string $courier, string $label, string $service, string $description, string $etd, int $cost): array
    {
        $courier = strtolower(trim($courier));
        $service = trim($service) ?: 'REG';
        $label = trim($label) ?: strtoupper($courier);
        $description = trim($description) ?: $service;
        $etd = trim($etd);

        return [
            'key' => $courier . '|' . $service . '|' . $cost,
            'courier' => $courier,
            'courier_label' => $label,
            'service' => $service,
            'description' => $description,
            'etd' => $etd,
            'cost' => $cost,
            'formatted_cost' => 'Rp ' . number_format($cost, 0, ',', '.'),
        ];
    }

    private function extractCost(array $row): int
    {
        if (isset($row['cost']) && is_numeric($row['cost'])) return (int) $row['cost'];
        if (isset($row['value']) && is_numeric($row['value'])) return (int) $row['value'];
        if (isset($row['cost'][0]['value']) && is_numeric($row['cost'][0]['value'])) return (int) $row['cost'][0]['value'];
        return 0;
    }

    private function request(string $method, string $url, ?string $body = null): array
    {
        $headers = [
            'Accept: application/json',
            'Content-Type: application/x-www-form-urlencoded',
            'key: ' . $this->apiKey(),
        ];

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
            if ($this->sslVerify() && $this->caInfo()) {
                $curlOptions[CURLOPT_CAINFO] = $this->caInfo();
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
                throw new RuntimeException('Gagal menghubungi RajaOngkir: ' . $error . '. Jika ini SSL lokal Windows, pastikan storage/certs/cacert.pem ada atau set RAJAONGKIR_SSL_VERIFY=false sementara untuk lokal.');
            }
        } else {
            $context = stream_context_create([
                'http' => [
                    'method' => $method,
                    'header' => implode("\r\n", $headers),
                    'content' => $body ?? '',
                    'timeout' => 30,
                    'ignore_errors' => true,
                ],
                'ssl' => [
                    'verify_peer' => $this->sslVerify(),
                    'verify_peer_name' => $this->sslVerify(),
                    'cafile' => $this->caInfo(),
                ],
            ]);
            $response = file_get_contents($url, false, $context);
            $status = 0;
            if (isset($http_response_header[0]) && preg_match('/\s(\d{3})\s/', $http_response_header[0], $m)) {
                $status = (int) $m[1];
            }
            if ($response === false) {
                throw new RuntimeException('Gagal menghubungi RajaOngkir. Pastikan internet server aktif.');
            }
        }

        $json = json_decode((string) $response, true);
        if (! is_array($json)) {
            throw new RuntimeException('Response RajaOngkir tidak valid: ' . mb_substr((string) $response, 0, 180));
        }

        if ($status >= 400 || (($json['meta']['status'] ?? true) === false)) {
            $message = $json['meta']['message'] ?? $json['message'] ?? 'Request RajaOngkir gagal.';
            throw new RuntimeException($message);
        }

        return $json;
    }
}
