<?php
namespace App\Http\Controllers;

use App\Models\{ProductReview, Product};
use Illuminate\Http\Request;

class ProductReviewController extends Controller
{
    private function userId() { return session('user_id'); }

    private function syncProductRating($productId): array
    {
        $reviews = ProductReview::where('product_id', $productId)->get();
        $avgRating = $reviews->count() ? round((float) $reviews->avg('rating'), 1) : 0;

        Product::where('id', $productId)->update([
            'rating' => $avgRating,
            'updated_at' => now(),
        ]);

        return [$avgRating, $reviews->count()];
    }

    public function store(Request $request, $productId)
    {
        if (!$this->userId()) {
            return response()->json([
                'ok' => false,
                'message' => 'Silakan login dulu untuk memberikan ulasan.',
                'redirect' => route('login')
            ], 401);
        }

        $data = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'body'   => 'required|string|max:1000',
        ]);

        $existingReview = ProductReview::where('product_id', $productId)
            ->where('user_id', $this->userId())
            ->first();

        if ($existingReview) {
            $existingReview->update([
                'rating' => $data['rating'],
                'body' => $data['body'],
                'updated_at' => now(),
                'is_edited' => 1,
            ]);
            $review = $existingReview->fresh('user');
            $message = 'Ulasan berhasil diedit!';
        } else {
            $review = ProductReview::create([
                'product_id' => $productId,
                'user_id' => $this->userId(),
                'rating' => $data['rating'],
                'body' => $data['body'],
                'is_edited' => 0,
            ])->load('user');
            $message = 'Ulasan berhasil disimpan!';
        }

        [$avgRating, $reviewCount] = $this->syncProductRating($productId);

        $isEdited = (bool) ($review->is_edited ?? false);
        $avatarUrl = null;
        if ($review->user && !empty($review->user->avatar)) {
            $avatar = trim((string) $review->user->avatar);
            $avatarUrl = (str_starts_with($avatar, 'http://') || str_starts_with($avatar, 'https://') || str_starts_with($avatar, '/'))
                ? $avatar
                : asset($avatar);
        }

        return response()->json([
            'ok' => true,
            'message' => $message,
            'mode' => $existingReview ? 'updated' : 'created',
            'review' => [
                'id' => $review->id,
                'rating' => $review->rating,
                'body' => $review->body,
                'user_name' => $review->user->name ?? 'Kamu',
                'user_role' => $review->user->role ?? 'user',
                'user_avatar' => $avatarUrl,
                'created_at' => $review->created_at->format('d M Y'),
                'updated_at' => $review->updated_at->format('d M Y'),
                'is_edited' => $isEdited,
            ],
            'avg_rating' => $avgRating,
            'review_count' => $reviewCount,
        ]);
    }

    public function destroy($productId)
    {
        if (!$this->userId()) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 401);
        }
        ProductReview::where('product_id', $productId)->where('user_id', $this->userId())->delete();
        [$avgRating, $reviewCount] = $this->syncProductRating($productId);

        return response()->json([
            'ok' => true,
            'message' => 'Ulasan dihapus.',
            'avg_rating' => $avgRating,
            'review_count' => $reviewCount,
        ]);
    }
}
