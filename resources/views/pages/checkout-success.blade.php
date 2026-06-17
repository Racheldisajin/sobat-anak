@extends('layouts.app')

@section('content')
<section class="min-h-screen bg-pink-50 flex items-center justify-center px-4">
    <div class="bg-white rounded-3xl shadow-lg p-8 max-w-md text-center">
        <div class="text-6xl mb-4">🎉</div>

        <h1 class="text-3xl font-bold text-pink-700 mb-3">
            Checkout Berhasil!
        </h1>

        <p class="text-gray-600 mb-6">
            Terima kasih sudah berbelanja di Sobat Anak.
        </p>

        <a href="{{ url('/products') }}" class="bg-pink-500 text-white px-6 py-3 rounded-full inline-block">
            Belanja Lagi
        </a>
    </div>
</section>
@endsection