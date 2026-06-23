<?php
namespace App\Http\Controllers;

use App\Models\UserAddress;
use Illuminate\Http\Request;

class UserAddressController extends Controller
{
    private function userId() { return session('user_id'); }

    public function store(Request $request)
    {
        if (!$this->userId()) {
            return response()->json(['ok' => false, 'message' => 'Silakan login dulu.', 'redirect' => route('login')], 401);
        }

        $data = $request->validate([
            'recipient_name' => 'required|string|max:255',
            'phone'          => 'required|string|max:30',
            'address'        => 'required|string|max:500',
            'city'           => 'required|string|max:100',
            'province'       => 'required|string|max:100',
            'postal_code'    => 'nullable|string|max:10',
        ]);

        $data['postal_code'] = $data['postal_code'] ?? '';
        $data['label'] = $data['label'] ?? 'Rumah';
        $data['is_default'] = 1;

        $address = UserAddress::updateOrCreate(
            ['user_id' => $this->userId()],
            $data
        );

        return response()->json([
            'ok' => true,
            'message' => 'Alamat berhasil disimpan!',
            'address' => $address,
        ]);
    }

    public function get()
    {
        if (!$this->userId()) {
            return response()->json(['ok' => false, 'address' => null]);
        }
        $address = UserAddress::where('user_id', $this->userId())->first();
        return response()->json(['ok' => true, 'address' => $address]);
    }
}
