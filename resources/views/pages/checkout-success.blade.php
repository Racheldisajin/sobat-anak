@extends('layouts.app')
@section('title','Status Checkout — SobatAnak')
@section('content')
@php($hasOrder = isset($order) && $order)
<section class="min-h-screen bg-gradient-to-br from-[#FDECEA] via-white to-[#D0F0ED] flex items-center justify-center px-4 py-12">
    <div class="checkout-result-card">
        <div class="checkout-result-icon">{{ $hasOrder && $order->status === 'paid' ? '🎉' : '🧾' }}</div>

        @if($hasOrder)
            <p class="checkout-result-kicker">Order {{ $order->order_number }}</p>
            <h1>{{ $order->status === 'paid' ? 'Pembayaran Berhasil!' : $order->status_label }}</h1>
            <p class="checkout-result-text">
                @if($order->status === 'paid')
                    Terima kasih sudah berbelanja di SobatAnak. Pesanan kamu sudah tercatat dan stok produk sudah diperbarui.
                @elseif(in_array($order->status, ['pending','challenge'], true))
                    Pembayaran masih menunggu konfirmasi Midtrans. Selesaikan pembayaran atau refresh status dari halaman payment.
                @else
                    Pembayaran belum berhasil. Kamu bisa kembali ke produk dan checkout ulang jika diperlukan.
                @endif
            </p>

            <div class="checkout-result-summary">
                <div><span>Status</span><b>{{ $order->status_label }}</b></div>
                <div><span>Total</span><b>Rp {{ number_format($order->total_amount,0,',','.') }}</b></div>
                <div><span>Metode dipilih</span><b>{{ $order->selected_payment_label ?: 'Semua Metode Aktif' }}</b></div>
                @if($order->payment_type)
                    <div><span>Metode Midtrans</span><b>{{ strtoupper(str_replace('_',' ', $order->payment_type)) }}</b></div>
                @endif
                @if($order->va_number)
                    <div><span>No. VA</span><b>{{ strtoupper($order->payment_bank ?: 'VA') }} • {{ $order->va_number }}</b></div>
                @elseif($order->payment_code)
                    <div><span>Kode bayar</span><b>{{ $order->payment_store ? strtoupper($order->payment_store).' • ' : '' }}{{ $order->payment_code }}</b></div>
                @endif
                @if($order->paid_at)
                    <div><span>Dibayar</span><b>{{ $order->paid_at->format('d M Y H:i') }}</b></div>
                @endif
            </div>

            <div class="checkout-result-actions">
                @if($order->status !== 'paid')
                    <a href="{{ route('checkout.payment', $order) }}" class="result-btn primary">Lihat Payment</a>
                @endif
                <a href="{{ route('products') }}" class="result-btn secondary">Belanja Lagi</a>
            </div>
        @else
            <h1>Checkout Berhasil!</h1>
            <p class="checkout-result-text">Terima kasih sudah berbelanja di Sobat Anak.</p>
            <a href="{{ route('products') }}" class="result-btn primary">Belanja Lagi</a>
        @endif
    </div>
</section>

<style>
.checkout-result-card{width:min(560px,100%);background:#fff;border:1px solid var(--border);border-radius:2rem;padding:2rem;text-align:center;box-shadow:0 24px 70px rgba(42,61,60,.12)}.checkout-result-icon{font-size:4rem;margin-bottom:.6rem}.checkout-result-kicker{font-size:.72rem;font-weight:1000;letter-spacing:.09em;text-transform:uppercase;color:var(--coral);margin:0}.checkout-result-card h1{font-size:2rem;font-weight:1000;color:var(--fg);margin:.25rem 0 .8rem}.checkout-result-text{color:var(--muted);font-weight:850;line-height:1.6}.checkout-result-summary{margin:1.2rem 0;display:grid;gap:.65rem;text-align:left}.checkout-result-summary div{display:flex;align-items:center;justify-content:space-between;gap:1rem;background:#F7FBFA;border:1px solid var(--border);border-radius:1rem;padding:.85rem 1rem;font-weight:900}.checkout-result-summary span{color:var(--muted)}.checkout-result-summary b{color:var(--fg)}.checkout-result-actions{display:flex;gap:.75rem;justify-content:center;flex-wrap:wrap}.result-btn{display:inline-flex;align-items:center;justify-content:center;border-radius:999px;padding:.9rem 1.25rem;font-weight:1000;transition:.22s}.result-btn.primary{background:var(--coral);color:#fff;box-shadow:0 14px 32px rgba(232,117,106,.22)}.result-btn.secondary{background:#D0F0ED;color:#2f9e92}.result-btn:hover{transform:translateY(-2px)}@media(max-width:640px){.checkout-result-card{border-radius:1.4rem;padding:1.35rem}.checkout-result-actions{flex-direction:column}.result-btn{width:100%}}
</style>
@endsection
