<?php
namespace App\Http\Controllers;

use App\Models\{ProductReview, Product};
use Illuminate\Http\Request;

class ProductReviewController extends Controller
{
    private function userId() { return session('user_id'); }

    public function store(Request $request, $productId)
    {
        if (!$this->userId()) {
            return response()->json(['ok' => false, 'message' => 'Silakan login dulu untuk memberikan ulasan.', 'redirect' => route('login')], 401);
        }

        $data = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'body'   => 'required|string|max:1000',
        ]);

        $review = ProductReview::updateOrCreate(
            ['product_id' => $productId, 'user_id' => $this->userId()],
            ['rating' => $data['rating'], 'body' => $data['body']]
        );

        $reviews = ProductReview::where('product_id', $productId)->get();
        $avgRating = round($reviews->avg('rating'), 1);

        return response()->json([
            'ok' => true,
            'message' => 'Ulasan berhasil disimpan!',
            'review' => [
                'id' => $review->id,
                'rating' => $review->rating,
                'body' => $review->body,
                'user_name' => $review->user->name ?? 'Kamu',
                'created_at' => $review->created_at->format('d M Y'),
            ],
            'avg_rating' => $avgRating,
            'review_count' => $reviews->count(),
        ]);
    }

    public function destroy($productId)
    {
        if (!$this->userId()) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 401);
        }
        ProductReview::where('product_id', $productId)->where('user_id', $this->userId())->delete();
        return response()->json(['ok' => true, 'message' => 'Ulasan dihapus.']);
    }
}
