<?php
namespace App\Http\Controllers;

use App\Models\{CartItem, Order, Product, User, UserAddress};
use App\Services\{MidtransSnapService, RajaOngkirService};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

class CartController extends Controller
{
    private function currentUser()
    {
        return User::find(session('user_id'));
    }

    private function requireUser()
    {
        $user = $this->currentUser();
        if (!$user) {
            return redirect()->route('login')->withErrors(['login' => 'Silakan login dulu untuk membuka keranjang.']);
        }
        return $user;
    }

    private function cartQuery(User $user)
    {
        return CartItem::with('product')->where('user_id', $user->id);
    }

    private function cartSummary(User $user): array
    {
        $cartItems = $this->cartQuery($user)->get();
        $subtotal = $cartItems->sum(fn($item) => ($item->product->price ?? 0) * $item->quantity);

        return [
            'subtotal' => $subtotal,
            'subtotal_formatted' => 'Rp ' . number_format($subtotal, 0, ',', '.'),
            'cart_count' => (int) $cartItems->sum('quantity'),
            'total_products' => (int) $cartItems->count(),
            'total_items_label' => $cartItems->sum('quantity') . ' item',
        ];
    }

    private function wantsJson(Request $request): bool
    {
        return $request->expectsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest';
    }

    public function index()
    {
        $user = $this->requireUser();
        if (! $user instanceof User) return $user;

        $cartItems = $this->cartQuery($user)->latest()->get();
        $subtotal = $cartItems->sum(fn($item) => ($item->product->price ?? 0) * $item->quantity);

        return view('pages.cart', compact('user', 'cartItems', 'subtotal'));
    }

    public function update(Request $request, CartItem $cartItem)
    {
        $user = $this->requireUser();
        if (! $user instanceof User) return $user;
        abort_unless((int) $cartItem->user_id === (int) $user->id, 403);

        $data = $request->validate(['quantity' => 'required|integer|min:1|max:99']);
        $stock = (int) ($cartItem->product->stock ?? 0);

        if ($stock <= 0) {
            $cartItem->delete();
            $message = 'Produk ini stoknya habis, jadi dihapus dari keranjang.';
            if ($this->wantsJson($request)) {
                return response()->json(['ok' => true, 'removed' => true, 'message' => $message] + $this->cartSummary($user));
            }
            return back()->with('success', $message);
        }

        $quantity = min((int) $data['quantity'], $stock);
        $cartItem->update(['quantity' => $quantity]);

        $lineTotal = ($cartItem->product->price ?? 0) * $quantity;
        $message = 'Jumlah produk berhasil diperbarui.';

        if ($this->wantsJson($request)) {
            return response()->json([
                'ok' => true,
                'message' => $message,
                'item_id' => $cartItem->id,
                'quantity' => $quantity,
                'line_total' => $lineTotal,
                'line_total_formatted' => 'Rp ' . number_format($lineTotal, 0, ',', '.'),
            ] + $this->cartSummary($user));
        }

        return back()->with('success', $message);
    }

    public function destroy(Request $request, CartItem $cartItem)
    {
        $user = $this->requireUser();
        if (! $user instanceof User) return $user;
        abort_unless((int) $cartItem->user_id === (int) $user->id, 403);

        $cartItem->delete();
        $message = 'Produk berhasil dihapus dari keranjang.';

        if ($this->wantsJson($request)) {
            return response()->json(['ok' => true, 'deleted' => true, 'message' => $message, 'item_id' => $cartItem->id] + $this->cartSummary($user));
        }

        return back()->with('success', $message);
    }

    public function checkout(RajaOngkirService $rajaOngkir)
    {
        $user = $this->requireUser();
        if (! $user instanceof User) return $user;

        $cartItems = $this->cartQuery($user)->latest()->get();
        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('success', 'Keranjang masih kosong. Tambahkan produk dulu sebelum checkout.');
        }

        $stockError = $this->firstStockError($cartItems);
        if ($stockError) {
            return redirect()->route('cart.index')->with('success', $stockError);
        }

        $address = UserAddress::where('user_id', $user->id)->latest()->first();
        $subtotal = $cartItems->sum(fn($item) => ($item->product->price ?? 0) * $item->quantity);
        $shippingCost = 0;
        $total = $subtotal + $shippingCost;
        $rajaOngkirConfigured = $rajaOngkir->isConfigured();
        $defaultWeight = $this->cartWeight($cartItems, $rajaOngkir);
        $shippingOptions = $rajaOngkirConfigured ? [] : $rajaOngkir->fallbackOptions((int) $subtotal, $defaultWeight);

        return view('pages.checkout', compact('user', 'cartItems', 'subtotal', 'shippingCost', 'total', 'address', 'shippingOptions', 'defaultWeight', 'rajaOngkirConfigured'));
    }

    public function shippingOptions(Request $request, RajaOngkirService $rajaOngkir)
    {
        $user = $this->requireUser();
        if (! $user instanceof User) return $user;

        $cartItems = $this->cartQuery($user)->latest()->get();
        if ($cartItems->isEmpty()) {
            return response()->json(['ok' => false, 'message' => 'Keranjang masih kosong.'], 422);
        }

        $data = $request->validate([
            'destination_id' => 'nullable|string|max:30',
            'district_name' => 'nullable|string|max:120',
            'city' => 'nullable|string|max:120',
            'province' => 'nullable|string|max:120',
            'postal_code' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'weight' => 'nullable|integer|min:1|max:30000',
        ]);

        try {
            $weight = (int) ($data['weight'] ?? $this->cartWeight($cartItems, $rajaOngkir));
            $resolved = $rajaOngkir->resolveDestinationId($data);
            $destinationId = $resolved['id'] ?? null;

            $options = $rajaOngkir->isConfigured() && filled($destinationId)
                ? $rajaOngkir->calculateDomestic((string) $destinationId, $weight)
                : $rajaOngkir->fallbackOptions((int) $cartItems->sum(fn($item) => ($item->product->price ?? 0) * $item->quantity), $weight);

            $usingReal = $rajaOngkir->isConfigured() && filled($destinationId);
            if (empty($options)) {
                $options = $rajaOngkir->fallbackOptions((int) $cartItems->sum(fn($item) => ($item->product->price ?? 0) * $item->quantity), $weight);
                $usingReal = false;
            }

            $message = $usingReal
                ? 'Ongkir tersedia' . (($resolved['label'] ?? null) ? ' untuk ' . $resolved['label'] : '') . '.'
                : 'Ongkir estimasi sementara ditampilkan. Coba lagi nanti jika ingin tarif RajaOngkir terbaru.';

            return response()->json([
                'ok' => true,
                'source' => $usingReal ? 'rajaongkir' : 'fallback',
                'message' => $message,
                'destination_id' => $destinationId,
                'destination_label' => $resolved['label'] ?? null,
                'options' => $options,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'ok' => true,
                'message' => str_contains(strtolower($e->getMessage()), 'limit')
                    ? 'Kuota cek ongkir harian RajaOngkir habis. Kami tampilkan estimasi ongkir sementara.'
                    : 'RajaOngkir sedang tidak bisa diakses. Kami tampilkan estimasi ongkir sementara.',
                'source' => 'fallback',
                'options' => $rajaOngkir->fallbackOptions((int) $cartItems->sum(fn($item) => ($item->product->price ?? 0) * $item->quantity), (int) ($data['weight'] ?? $this->cartWeight($cartItems, $rajaOngkir))),
             ]);
        }
    }

    private function normalizeAddressText(?string $value): string
    {
        $value = Str::ascii((string) $value);
        $value = strtoupper($value);
        $value = preg_replace('/[^A-Z0-9\s]/', ' ', $value) ?? '';
        $value = preg_replace('/\s+/', ' ', $value) ?? '';
        return trim($value);
    }

    private function addressTokens(?string $value): array
    {
        $text = $this->normalizeAddressText($value);
        if ($text === '') return [];

        $stopwords = [
            'JL', 'JLN', 'JALAN', 'GG', 'GANG', 'NO', 'NOMOR', 'BLOK', 'KAV', 'KAVLING',
            'RT', 'RW', 'KP', 'KAMPUNG', 'KOMP', 'KOMPLEK', 'PERUM', 'PERUMAHAN',
            'DESA', 'KEL', 'KELURAHAN', 'KEC', 'KECAMATAN', 'KAB', 'KABUPATEN',
            'KOTA', 'PROV', 'PROVINSI', 'INDONESIA', 'RAYA', 'DALAM', 'BARAT', 'TIMUR',
            'UTARA', 'SELATAN', 'TENGAH'
        ];

        return collect(explode(' ', $text))
            ->map(fn ($token) => trim($token))
            ->filter(fn ($token) => strlen($token) >= 3 && ! in_array($token, $stopwords, true))
            ->unique()
            ->values()
            ->all();
    }

    private function buildAddressSearchQueries(array $data): array
    {
        $queryTokens = $this->addressTokens($data['q'] ?? '');
        $cleanQuery = trim(implode(' ', $queryTokens));
        $district = trim((string) ($data['district_name'] ?? ''));
        $city = trim((string) ($data['city'] ?? ''));
        $province = trim((string) ($data['province'] ?? ''));
        $postal = trim((string) ($data['postal_code'] ?? ''));

        $queries = [];
        if ($cleanQuery !== '') {
            $queries[] = trim($cleanQuery . ' ' . $city . ' ' . $postal);
            $queries[] = trim($cleanQuery . ' ' . $city);
            $queries[] = $cleanQuery;
        }
        if ($district !== '' || $city !== '' || $postal !== '') {
            $queries[] = trim($district . ' ' . $city . ' ' . $postal);
            $queries[] = trim($city . ' ' . $postal);
            $queries[] = trim($district . ' ' . $city);
        }
        if ($postal !== '') $queries[] = $postal;
        if ($city !== '') $queries[] = $city;

        return collect($queries)
            ->map(fn ($q) => preg_replace('/\s+/', ' ', trim((string) $q)))
            ->filter(fn ($q) => strlen($q) >= 3)
            ->unique()
            ->take(7)
            ->values()
            ->all();
    }

    private function labelFromRajaRow(array $row): string
    {
        return (string) ($row['label'] ?? trim(implode(', ', array_filter([
            $row['subdistrict_name'] ?? null,
            $row['district_name'] ?? null,
            $row['city_name'] ?? null,
            $row['province_name'] ?? null,
            $row['zip_code'] ?? null,
        ]))));
    }

    private function scoreAddressRow(array $row, array $data): array
    {
        $label = $this->normalizeAddressText($this->labelFromRajaRow($row));
        $haystack = $this->normalizeAddressText(implode(' ', [
            $label,
            $row['province_name'] ?? '',
            $row['city_name'] ?? '',
            $row['district_name'] ?? '',
            $row['subdistrict_name'] ?? '',
            $row['zip_code'] ?? '',
        ]));

        $queryTokens = $this->addressTokens($data['q'] ?? '');
        $tokenMatches = 0;
        foreach ($queryTokens as $token) {
            if (str_contains($haystack, $token)) {
                $tokenMatches++;
            }
        }

        $score = $tokenMatches * 12;
        $postal = $this->normalizeAddressText($data['postal_code'] ?? '');
        $districtTokens = $this->addressTokens($data['district_name'] ?? '');
        $cityTokens = $this->addressTokens($data['city'] ?? '');
        $provinceTokens = $this->addressTokens($data['province'] ?? '');

        $contextScore = 0;
        if ($postal !== '' && str_contains($haystack, $postal)) { $score += 25; $contextScore += 25; }
        foreach ($districtTokens as $token) { if (str_contains($haystack, $token)) { $score += 18; $contextScore += 18; } }
        foreach ($cityTokens as $token) { if (str_contains($haystack, $token)) { $score += 14; $contextScore += 14; } }
        foreach ($provinceTokens as $token) { if (str_contains($haystack, $token)) { $score += 8; $contextScore += 8; } }

        // Jangan tampilkan hasil nyasar seperti "Jalan Lurus" saat user mengetik nama jalan yang tidak ada di database RajaOngkir.
        $hasUsefulMatch = $tokenMatches > 0 || $contextScore >= 14 || count($queryTokens) === 0;

        return [$score, $hasUsefulMatch];
    }

    public function addressSearch(Request $request, RajaOngkirService $rajaOngkir)
    {
        $user = $this->requireUser();
        if (! $user instanceof User) return $user;

        $data = $request->validate([
            'q' => 'required|string|min:3|max:160',
            'city' => 'nullable|string|max:100',
            'province' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:10',
            'district_name' => 'nullable|string|max:120',
        ]);

        $items = [];
        $osmItems = [];
        $rajaError = null;
        $osmError = null;

        // RajaOngkir dipakai untuk destination_id ongkir. Jika kuota RajaOngkir limit,
        // dropdown alamat tetap berjalan memakai OpenStreetMap supaya user tidak stuck.
        try {
            $queries = $this->buildAddressSearchQueries($data);
            $rowsById = [];

            foreach ($queries as $query) {
                foreach ($rajaOngkir->searchDomesticDestinations($query, 20) as $row) {
                    $id = (string) ($row['id'] ?? '');
                    if ($id === '') continue;
                    [$score, $useful] = $this->scoreAddressRow($row, $data);
                    if (! $useful) continue;
                    if (! isset($rowsById[$id]) || $score > $rowsById[$id]['_score']) {
                        $row['_score'] = $score;
                        $rowsById[$id] = $row;
                    }
                }
            }

            $items = collect(array_values($rowsById))
                ->sortByDesc('_score')
                ->take(10)
                ->map(function ($row) {
                    $label = $this->labelFromRajaRow($row);
                    return [
                        'id' => (string) ($row['id'] ?? ''),
                        'label' => $label,
                        'province' => (string) ($row['province_name'] ?? ''),
                        'city' => (string) ($row['city_name'] ?? ''),
                        'district' => (string) ($row['district_name'] ?? ''),
                        'subdistrict' => (string) ($row['subdistrict_name'] ?? ''),
                        'postal_code' => (string) ($row['zip_code'] ?? ''),
                        'latitude' => '',
                        'longitude' => '',
                        'maps_url' => 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($label),
                        'source' => 'rajaongkir',
                    ];
                })
                ->filter(fn ($row) => filled($row['id']) && filled($row['label']))
                ->values()
                ->all();
        } catch (Throwable $e) {
            $rajaError = $e->getMessage();
        }

        try {
            $osmItems = $this->searchOpenStreetMapAddresses($data['q'], $rajaOngkir, 10);
        } catch (Throwable $e) {
            $osmError = $e->getMessage();
        }

        $merged = collect($osmItems)
            ->merge($items)
            ->unique(function ($row) {
                return strtolower(trim(($row['label'] ?? '') . '|' . ($row['latitude'] ?? '') . '|' . ($row['longitude'] ?? '') . '|' . ($row['id'] ?? '')));
            })
            ->take(10)
            ->values()
            ->all();

        $message = empty($merged) ? 'Alamat belum cocok. Coba ketik nama jalan + kota/kecamatan.' : null;
        if (! empty($merged) && $rajaError) {
            $message = 'Alamat ditemukan. RajaOngkir sedang limit, destination_id akan dicari lagi saat cek ongkir.';
        }

        return response()->json([
            'ok' => true,
            'items' => $merged,
            'message' => $message,
            'raja_error' => $rajaError,
            'osm_error' => $osmError,
        ]);
    }


    private function nominatimRequest(string $url): array
    {
        $headers = [
            'Accept: application/json',
            'User-Agent: SobatAnakCheckout/1.0 (local development)',
        ];

        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_TIMEOUT => 12,
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_SSL_VERIFYPEER => false,
            ]);
            $response = curl_exec($ch);
            curl_close($ch);
        } else {
            $response = @file_get_contents($url, false, stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'header' => implode("\r\n", $headers),
                    'timeout' => 12,
                    'ignore_errors' => true,
                ],
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                ],
            ]));
        }

        $json = json_decode((string) $response, true);
        return is_array($json) ? $json : [];
    }

    private function cleanAddressPart(?string $value): string
    {
        return trim((string) $value);
    }

    private function mapNominatimItem(array $row, RajaOngkirService $rajaOngkir): ?array
    {
        $address = (array) ($row['address'] ?? []);
        $province = $this->cleanAddressPart($address['state'] ?? $address['province'] ?? '');
        $city = $this->cleanAddressPart($address['city'] ?? $address['town'] ?? $address['county'] ?? $address['municipality'] ?? $address['regency'] ?? '');
        $district = $this->cleanAddressPart($address['city_district'] ?? $address['district'] ?? $address['suburb'] ?? $address['village'] ?? $address['subdistrict'] ?? '');
        $subdistrict = $this->cleanAddressPart($address['neighbourhood'] ?? $address['quarter'] ?? $address['hamlet'] ?? $address['village'] ?? '');
        $postal = $this->cleanAddressPart($address['postcode'] ?? '');
        $road = $this->cleanAddressPart($address['road'] ?? $address['pedestrian'] ?? $address['residential'] ?? '');
        $house = $this->cleanAddressPart($address['house_number'] ?? '');
        $display = $this->cleanAddressPart($row['display_name'] ?? '');

        if ($display === '') {
            return null;
        }

        $streetLine = trim(implode(' ', array_filter([$road, $house])));
        $label = $streetLine !== '' ? trim($streetLine . ', ' . $display) : $display;

        $destination = null;
        try {
            $destination = $rajaOngkir->resolveDestinationId([
                'district_name' => $district ?: $subdistrict,
                'city' => $city,
                'province' => $province,
                'postal_code' => $postal,
                'address' => $label,
            ]);
        } catch (Throwable $e) {
            $destination = null;
        }

        return [
            'id' => (string) ($destination['id'] ?? ''),
            'label' => $label,
            'province' => $province,
            'city' => $city,
            'district' => $district ?: $subdistrict,
            'subdistrict' => $subdistrict,
            'postal_code' => $postal,
            'latitude' => (string) ($row['lat'] ?? ''),
            'longitude' => (string) ($row['lon'] ?? ''),
            'maps_url' => 'https://www.google.com/maps?q=' . rawurlencode(($row['lat'] ?? '') . ',' . ($row['lon'] ?? '')),
            'source' => 'osm',
        ];
    }

    private function searchOpenStreetMapAddresses(string $query, RajaOngkirService $rajaOngkir, int $limit = 8): array
    {
        $url = 'https://nominatim.openstreetmap.org/search?' . http_build_query([
            'format' => 'jsonv2',
            'addressdetails' => 1,
            'limit' => max(1, min(10, $limit)),
            'countrycodes' => 'id',
            'accept-language' => 'id',
            'q' => $query . ', Indonesia',
        ]);

        return collect($this->nominatimRequest($url))
            ->map(fn ($row) => is_array($row) ? $this->mapNominatimItem($row, $rajaOngkir) : null)
            ->filter()
            ->values()
            ->all();
    }

    public function reverseGeocode(Request $request, RajaOngkirService $rajaOngkir)
    {
        $user = $this->requireUser();
        if (! $user instanceof User) return $user;

        $data = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        try {
            $url = 'https://nominatim.openstreetmap.org/reverse?' . http_build_query([
                'format' => 'jsonv2',
                'addressdetails' => 1,
                'zoom' => 18,
                'accept-language' => 'id',
                'lat' => $data['latitude'],
                'lon' => $data['longitude'],
            ]);
            $row = $this->nominatimRequest($url);
            $item = is_array($row) ? $this->mapNominatimItem($row, $rajaOngkir) : null;

            if (! $item) {
                $item = [
                    'id' => '',
                    'label' => 'Titik koordinat: ' . $data['latitude'] . ', ' . $data['longitude'],
                    'province' => '',
                    'city' => '',
                    'district' => '',
                    'subdistrict' => '',
                    'postal_code' => '',
                    'latitude' => (string) $data['latitude'],
                    'longitude' => (string) $data['longitude'],
                    'maps_url' => 'https://www.google.com/maps?q=' . rawurlencode($data['latitude'] . ',' . $data['longitude']),
                    'source' => 'coordinate',
                ];
            }

            return response()->json(['ok' => true, 'item' => $item]);
        } catch (Throwable $e) {
            return response()->json([
                'ok' => false,
                'message' => 'Gagal membaca alamat dari titik peta. Pin tetap tersimpan, alamat bisa diisi manual.',
            ], 422);
        }
    }

    public function pay(Request $request, MidtransSnapService $midtrans, RajaOngkirService $rajaOngkir)
    {
        $user = $this->requireUser();
        if (! $user instanceof User) return $user;

        if (! $midtrans->isConfigured()) {
            return back()->withErrors(['midtrans' => 'MIDTRANS_SERVER_KEY dan MIDTRANS_CLIENT_KEY belum diisi di file .env. Isi dulu dari dashboard Midtrans Sandbox.'])->withInput();
        }

        $data = $request->validate([
            'recipient_name' => 'required|string|max:255',
            'phone' => 'required|string|max:30',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'province' => 'required|string|max:100',
            'postal_code' => 'required|string|max:10',
            'destination_id' => 'nullable|string|max:30',
            'district_name' => 'nullable|string|max:120',
            'location_url' => 'nullable|url|max:1000',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'shipping_option' => 'required|string|max:255',
            'shipping_cost' => 'nullable|integer|min:0|max:500000',
            'customer_note' => 'nullable|string|max:500',
        ]);

        $cartItems = $this->cartQuery($user)->latest()->get();
        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('success', 'Keranjang masih kosong. Tambahkan produk dulu sebelum checkout.');
        }

        $stockError = $this->firstStockError($cartItems);
        if ($stockError) {
            return redirect()->route('cart.index')->with('success', $stockError);
        }

        try {
            $weight = $this->cartWeight($cartItems, $rajaOngkir);
            $resolvedDestination = $rajaOngkir->resolveDestinationId($data);
            if ($resolvedDestination && ! filled($data['destination_id'] ?? null)) {
                $data['destination_id'] = $resolvedDestination['id'];
            }

            $subtotalForShipping = (int) $cartItems->sum(fn($item) => ($item->product->price ?? 0) * $item->quantity);
            try {
                $availableOptions = $rajaOngkir->isConfigured() && filled($data['destination_id'] ?? null)
                    ? $rajaOngkir->calculateDomestic((string) $data['destination_id'], $weight)
                    : $rajaOngkir->fallbackOptions($subtotalForShipping, $weight);
            } catch (Throwable $shippingError) {
                $availableOptions = $rajaOngkir->fallbackOptions($subtotalForShipping, $weight);
            }

            $shippingOption = $rajaOngkir->findOption($availableOptions, $data['shipping_option']);
            if (! $shippingOption) {
                return back()->withErrors(['shipping' => 'Pilihan jasa kirim tidak valid. Klik Cek Ongkir lalu pilih ulang jasa kirim.'])->withInput();
            }

            $order = DB::transaction(function () use ($user, $cartItems, $data, $shippingOption, $weight, $midtrans) {
                $addressPayload = [
                    'label' => 'Rumah',
                    'recipient_name' => $data['recipient_name'],
                    'phone' => $data['phone'],
                    'address' => $data['address'],
                    'city' => $data['city'],
                    'province' => $data['province'],
                    'postal_code' => $data['postal_code'],
                    'is_default' => 1,
                ];

                if (Schema::hasColumn('user_addresses', 'location_url')) $addressPayload['location_url'] = $data['location_url'] ?? null;
                if (Schema::hasColumn('user_addresses', 'latitude')) $addressPayload['latitude'] = $data['latitude'] ?? null;
                if (Schema::hasColumn('user_addresses', 'longitude')) $addressPayload['longitude'] = $data['longitude'] ?? null;
                if (Schema::hasColumn('user_addresses', 'destination_id')) $addressPayload['destination_id'] = $data['destination_id'] ?? null;
                if (Schema::hasColumn('user_addresses', 'district_name')) $addressPayload['district_name'] = $data['district_name'] ?? null;

                $address = UserAddress::updateOrCreate(['user_id' => $user->id], $addressPayload);

                $subtotal = $cartItems->sum(fn($item) => ($item->product->price ?? 0) * $item->quantity);
                $shippingCost = (int) ($shippingOption['cost'] ?? 0);
                $total = $subtotal + $shippingCost;
                $expiryMinutes = $midtrans->expiryMinutes();

                $shippingSnapshot = [
                    'label' => 'Rumah',
                    'recipient_name' => $data['recipient_name'],
                    'phone' => $data['phone'],
                    'address' => $data['address'],
                    'city' => $data['city'],
                    'province' => $data['province'],
                    'postal_code' => $data['postal_code'],
                    'destination_id' => $data['destination_id'] ?? null,
                    'district_name' => $data['district_name'] ?? null,
                    'location_url' => $data['location_url'] ?? null,
                    'latitude' => $data['latitude'] ?? null,
                    'longitude' => $data['longitude'] ?? null,
                    'weight' => $weight,
                    'courier' => $shippingOption['courier'] ?? null,
                    'courier_label' => $shippingOption['courier_label'] ?? null,
                    'service' => $shippingOption['service'] ?? null,
                    'description' => $shippingOption['description'] ?? null,
                    'etd' => $shippingOption['etd'] ?? null,
                    'shipping_key' => $shippingOption['key'] ?? null,
                ];

                $order = Order::create([
                    'user_id' => $user->id,
                    'user_address_id' => $address->id,
                    'order_number' => $this->generateOrderNumber($user),
                    'subtotal' => $subtotal,
                    'shipping_cost' => $shippingCost,
                    'total_amount' => $total,
                    'status' => 'pending',
                    'payment_status' => 'pending',
                    'selected_payment_method' => 'official_snap',
                    'selected_payment_label' => 'Midtrans Official Snap',
                    'enabled_payments' => $midtrans->officialSnapPayments(),
                    'shipping_snapshot' => $shippingSnapshot,
                    'customer_note' => $data['customer_note'] ?? null,
                    'expired_at' => now()->addMinutes($expiryMinutes),
                ]);

                foreach ($cartItems as $item) {
                    $product = $item->product;
                    $price = (int) ($product->price ?? 0);
                    $qty = (int) $item->quantity;
                    $order->items()->create([
                        'product_id' => $product->id,
                        'product_name' => (string) $product->name,
                        'product_image' => (string) $product->image,
                        'price' => $price,
                        'quantity' => $qty,
                        'line_total' => $price * $qty,
                    ]);
                }

                return $order;
            });

            $snap = $midtrans->createSnapTransaction($order->fresh(['items', 'user']));
            $order->update([
                'snap_token' => $snap['token'] ?? null,
                'snap_redirect_url' => $snap['redirect_url'] ?? null,
                'midtrans_response' => $snap,
            ]);

            return redirect()->route('checkout.payment', $order)->with('success', 'Order berhasil dibuat. Cek totalnya, lalu klik Konfirmasi Bayar untuk membuka Midtrans Official Snap.');
        } catch (Throwable $e) {
            return back()->withErrors(['midtrans' => 'Gagal membuat pembayaran Midtrans: ' . $e->getMessage()])->withInput();
        }
    }

    private function cartWeight($cartItems, RajaOngkirService $rajaOngkir): int
    {
        // Produk SobatAnak belum punya kolom berat, jadi default 1 kg per item dari env.
        // Nanti kalau tabel products punya kolom weight/weight_gram, sistem otomatis pakai kolom itu.
        $default = $rajaOngkir->defaultWeight();
        $total = 0;

        foreach ($cartItems as $item) {
            $product = $item->product;
            $weight = 0;
            if ($product) {
                $weight = (int) ($product->weight_gram ?? $product->weight ?? 0);
            }
            $total += max(1, $weight ?: $default) * (int) $item->quantity;
        }

        return max(1, $total ?: $default);
    }

    private function firstStockError($cartItems): ?string
    {
        foreach ($cartItems as $item) {
            if (! $item->product) {
                return 'Ada produk yang sudah tidak tersedia. Hapus produk tersebut dari keranjang dulu.';
            }

            $stock = (int) ($item->product->stock ?? 0);
            if ($stock <= 0) {
                return 'Produk "' . $item->product->name . '" stoknya habis. Hapus dari keranjang dulu ya.';
            }

            if ((int) $item->quantity > $stock) {
                return 'Jumlah "' . $item->product->name . '" melebihi stok. Kurangi quantity dulu ya.';
            }
        }

        return null;
    }

    private function generateOrderNumber(User $user): string
    {
        do {
            $number = 'SA-' . now()->format('YmdHis') . '-U' . $user->id . '-' . strtoupper(Str::random(4));
        } while (Order::where('order_number', $number)->exists());

        return $number;
    }
}
