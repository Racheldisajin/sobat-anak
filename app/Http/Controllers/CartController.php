<?php
namespace App\Http\Controllers;

use App\Models\{CartItem,User};
use Illuminate\Http\Request;

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

    public function checkout()
    {
        $user = $this->requireUser();
        if (! $user instanceof User) return $user;

        $cartItems = $this->cartQuery($user)->latest()->get();
        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('success', 'Keranjang masih kosong. Tambahkan produk dulu sebelum checkout.');
        }

        $subtotal = $cartItems->sum(fn($item) => ($item->product->price ?? 0) * $item->quantity);
        return view('pages.checkout', compact('user', 'cartItems', 'subtotal'));
    }
}
