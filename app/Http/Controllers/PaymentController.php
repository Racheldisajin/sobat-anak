<?php
namespace App\Http\Controllers;

use App\Models\{CartItem, Order, Product, User};
use App\Services\MidtransSnapService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class PaymentController extends Controller
{
    private MidtransSnapService $midtrans;

    public function __construct(MidtransSnapService $midtrans)
    {
        $this->midtrans = $midtrans;
    }

    private function currentUser()
    {
        return User::find(session('user_id'));
    }

    private function requireUser()
    {
        $user = $this->currentUser();
        if (! $user) {
            return redirect()->route('login')->withErrors(['login' => 'Silakan login dulu untuk melihat pembayaran.']);
        }
        return $user;
    }

    private function assertOwner(Order $order, User $user): void
    {
        abort_unless((int) $order->user_id === (int) $user->id, 403);
    }

    public function show(Order $order)
    {
        $user = $this->requireUser();
        if (! $user instanceof User) return $user;
        $this->assertOwner($order, $user);

        // Saat user kembali ke halaman payment, coba sinkronkan status terbaru.
        // Ini membuat VA/kode bayar/QRIS yang sudah dibuat Midtrans bisa langsung tampil.
        try {
            if ($this->midtrans->isConfigured() && in_array($order->status, ['pending', 'challenge'], true)) {
                $payload = $this->midtrans->checkStatus($order->order_number);
                $this->applyMidtransPayload($order, $payload, false);
            }
        } catch (Throwable $e) {
            Log::info('Midtrans show status sync skipped', [
                'order' => $order->order_number,
                'error' => $e->getMessage(),
            ]);
        }

        $this->markExpiredIfNeeded($order);
        $order->refresh()->load('items');

        return view('pages.payment-midtrans', [
            'user' => $user,
            'order' => $order,
            'snapJsUrl' => $this->midtrans->snapJsUrl(),
            'midtransClientKey' => $this->midtrans->clientKey(),
            'isMidtransReady' => $this->midtrans->isConfigured(),
        ]);
    }

    public function finish(Request $request, Order $order)
    {
        $user = $this->requireUser();
        if (! $user instanceof User) return $user;
        $this->assertOwner($order, $user);

        try {
            if ($this->midtrans->isConfigured() && in_array($order->status, ['pending', 'challenge'], true)) {
                $payload = $this->midtrans->checkStatus($order->order_number);
                $this->applyMidtransPayload($order, $payload, false);
            }
        } catch (Throwable $e) {
            Log::warning('Midtrans finish status check failed', [
                'order' => $order->order_number,
                'error' => $e->getMessage(),
            ]);
        }

        $this->markExpiredIfNeeded($order);
        $order->refresh()->load('items');

        // Kalau transaksi masih pending/challenge, kembalikan user ke halaman payment
        // supaya nomor VA/kode bayar/QRIS dan status menunggu pembayaran terlihat jelas.
        if (in_array($order->status, ['pending', 'challenge'], true)) {
            return redirect()
                ->route('checkout.payment', $order)
                ->with('success', 'Pembayaran sudah dibuat. Selesaikan instruksi pembayaran yang muncul di halaman ini.');
        }

        if (in_array($order->status, ['expired', 'failed', 'cancelled'], true)) {
            return redirect()
                ->route('checkout.payment', $order)
                ->with('success', 'Waktu pembayaran habis atau pembayaran tidak aktif. Silakan kembali ke checkout untuk membuat pembayaran baru.');
        }

        return view('pages.checkout-success', [
            'user' => $user,
            'order' => $order,
        ]);
    }

    public function checkStatus(Order $order)
    {
        $user = $this->requireUser();
        if (! $user instanceof User) return $user;
        $this->assertOwner($order, $user);

        try {
            $payload = $this->midtrans->checkStatus($order->order_number);
            $this->applyMidtransPayload($order, $payload, false);
            $order->refresh();
            $expiredByLocalTimer = $this->markExpiredIfNeeded($order);
            $order->refresh();

            return response()->json([
                'ok' => true,
                'message' => $expiredByLocalTimer ? 'Waktu pembayaran 6 menit sudah habis.' : 'Status pembayaran berhasil diperbarui.',
                'status' => $order->status,
                'status_label' => $order->status_label,
                'paid_at' => optional($order->paid_at)->format('d M Y H:i'),
            ]);
        } catch (Throwable $e) {
            if ($this->markExpiredIfNeeded($order)) {
                $order->refresh();
                return response()->json([
                    'ok' => true,
                    'message' => 'Waktu pembayaran 6 menit sudah habis.',
                    'status' => $order->status,
                    'status_label' => $order->status_label,
                    'paid_at' => optional($order->paid_at)->format('d M Y H:i'),
                ]);
            }

            return response()->json([
                'ok' => false,
                'message' => 'Gagal cek status Midtrans: ' . $e->getMessage(),
            ], 422);
        }
    }

    public function notification(Request $request)
    {
        $payload = $request->all();

        if (! $this->midtrans->verifySignature($payload)) {
            Log::warning('Midtrans notification invalid signature', ['payload' => $payload]);
            return response()->json(['ok' => false, 'message' => 'Invalid signature'], 403);
        }

        $order = Order::where('order_number', $payload['order_id'] ?? null)->first();
        if (! $order) {
            Log::warning('Midtrans notification order not found', ['payload' => $payload]);
            return response()->json(['ok' => false, 'message' => 'Order tidak ditemukan'], 404);
        }

        $grossAmount = isset($payload['gross_amount']) ? (int) round((float) $payload['gross_amount']) : 0;
        if ($grossAmount !== (int) $order->total_amount) {
            Log::warning('Midtrans notification amount mismatch', [
                'order' => $order->order_number,
                'expected' => $order->total_amount,
                'actual' => $payload['gross_amount'] ?? null,
            ]);
            return response()->json(['ok' => false, 'message' => 'Amount tidak sesuai'], 422);
        }

        $this->applyMidtransPayload($order, $payload, true);

        return response()->json(['ok' => true]);
    }

    private function markExpiredIfNeeded(Order $order): bool
    {
        $order->refresh();

        if (! in_array($order->status, ['pending', 'challenge'], true)) {
            return false;
        }

        if (! $order->expired_at || now()->lt($order->expired_at)) {
            return false;
        }

        $order->forceFill([
            'status' => 'expired',
            'payment_status' => 'expire',
        ])->save();

        return true;
    }

    private function applyMidtransPayload(Order $order, array $payload, bool $fromWebhook): void
    {
        DB::transaction(function () use ($order, $payload, $fromWebhook) {
            $locked = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            $newStatus = $this->midtrans->mapOrderStatus($payload['transaction_status'] ?? null, $payload['fraud_status'] ?? null);

            // Kalau timer lokal 6 menit sudah membuat order expired, jangan ubah balik ke pending
            // hanya karena Midtrans status check masih pending. Status paid tetap boleh masuk.
            if (in_array($locked->status, ['expired', 'cancelled', 'failed'], true) && $newStatus === 'pending') {
                $newStatus = $locked->status;
            }

            $isFirstPaid = $newStatus === 'paid' && $locked->paid_at === null;
            $detail = $this->extractPaymentDetail($payload);

            $locked->fill([
                'status' => $newStatus,
                'payment_status' => $payload['transaction_status'] ?? $locked->payment_status,
                'payment_type' => $payload['payment_type'] ?? $locked->payment_type,
                'fraud_status' => $payload['fraud_status'] ?? $locked->fraud_status,
                'midtrans_transaction_id' => $payload['transaction_id'] ?? $locked->midtrans_transaction_id,
                'midtrans_order_id' => $payload['order_id'] ?? $locked->midtrans_order_id,
                'payment_bank' => $detail['bank'] ?? $locked->payment_bank,
                'payment_store' => $detail['store'] ?? $locked->payment_store,
                'payment_code' => $detail['payment_code'] ?? $locked->payment_code,
                'va_number' => $detail['va_number'] ?? $locked->va_number,
                'biller_code' => $detail['biller_code'] ?? $locked->biller_code,
                'bill_key' => $detail['bill_key'] ?? $locked->bill_key,
                'acquirer' => $detail['acquirer'] ?? $locked->acquirer,
                'pdf_url' => $detail['pdf_url'] ?? $locked->pdf_url,
                'payment_detail' => $detail ?: $locked->payment_detail,
                'midtrans_response' => $fromWebhook ? $locked->midtrans_response : $payload,
                'callback_payload' => $fromWebhook ? $payload : $locked->callback_payload,
            ]);

            if ($isFirstPaid) {
                $locked->paid_at = now();
            }

            $locked->save();

            if ($isFirstPaid) {
                $this->fulfillPaidOrder($locked);
            }
        });
    }

    private function extractPaymentDetail(array $payload): array
    {
        return $this->midtrans->paymentDetailFromPayload($payload);
    }

    private function fulfillPaidOrder(Order $order): void
    {
        $order->loadMissing('items');

        foreach ($order->items as $item) {
            if (! $item->product_id) {
                continue;
            }

            $product = Product::whereKey($item->product_id)->lockForUpdate()->first();
            if (! $product) {
                continue;
            }

            $product->stock = max(0, (int) $product->stock - (int) $item->quantity);
            $product->sold = (int) ($product->sold ?? 0) + (int) $item->quantity;
            $product->save();
        }

        CartItem::where('user_id', $order->user_id)
            ->whereIn('product_id', $order->items->pluck('product_id')->filter()->all())
            ->delete();
    }
}
