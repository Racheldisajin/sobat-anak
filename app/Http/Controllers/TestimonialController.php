<?php
namespace App\Http\Controllers;

use App\Models\{Testimonial, TestimonialLike, User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TestimonialController extends Controller
{
    private function authUser(): ?User
    {
        $id = session('user_id');
        return $id ? User::find($id) : null;
    }


    private function userAvatarUrl(?User $user): ?string
    {
        if (!$user || empty($user->avatar)) {
            return null;
        }

        $avatar = trim((string) $user->avatar);
        if (str_starts_with($avatar, 'http://') || str_starts_with($avatar, 'https://') || str_starts_with($avatar, '/')) {
            return $avatar;
        }

        return asset($avatar);
    }

    public function index(Request $request)
    {
        $authUser = $this->authUser();

        $rating = $request->query('rating', 'all');
        $sort = $request->query('sort', 'liked');

        $baseQuery = Testimonial::query()->with('user')->withCount('likes as real_likes_count');

        if (in_array((string) $rating, ['1', '2', '3', '4', '5'], true)) {
            $baseQuery->where('rating', (int) $rating);
        }

        if ($sort === 'newest') {
            $baseQuery->latest();
        } else {
            $baseQuery->orderByDesc('real_likes_count')->orderByDesc('created_at');
        }

        $testimonials = $baseQuery
            ->paginate(8)
            ->withQueryString();

        $ratingCounts = Testimonial::query()
            ->select('rating', DB::raw('COUNT(*) as total'))
            ->groupBy('rating')
            ->pluck('total', 'rating')
            ->toArray();

        $totalReviews = Testimonial::count();
        $averageRating = $totalReviews > 0 ? round((float) Testimonial::avg('rating'), 1) : 0;

        $likedTestimonialIds = [];
        if ($authUser) {
            $likedTestimonialIds = TestimonialLike::where('user_id', $authUser->id)
                ->pluck('testimonial_id')
                ->all();
        }

        return view('pages.testimonials', [
            'testimonials' => $testimonials,
            'ratingCounts' => $ratingCounts,
            'totalReviews' => $totalReviews,
            'averageRating' => $averageRating,
            'activeRating' => $rating,
            'activeSort' => $sort,
            'likedTestimonialIds' => $likedTestimonialIds,
            'authUser' => $authUser,
        ]);
    }

    public function store(Request $request)
    {
        $user = $this->authUser();
        if (!$user) {
            return redirect()->route('login')->withErrors(['login' => 'Silakan login dulu untuk menulis ulasan website.']);
        }

        $data = $request->validate([
            'message' => 'required|string|min:3|max:400',
            'rating' => 'nullable|integer|min:1|max:5',
        ]);

        Testimonial::create([
            'user_id' => $user->id,
            'name' => $user->name,
            'message' => $data['message'],
            'rating' => $data['rating'] ?? 5,
            'likes_count' => 0,
            'is_edited' => 0,
        ]);

        return back()->with('success', 'Ulasan kamu berhasil dikirim. Terima kasih sudah berbagi pengalaman!');
    }


    public function update(Request $request, Testimonial $testimonial)
    {
        $user = $this->authUser();
        if (!$user) {
            return redirect()->route('login')->withErrors(['login' => 'Silakan login dulu untuk mengedit ulasan website.']);
        }

        if ((int) $testimonial->user_id !== (int) $user->id) {
            abort(403, 'Kamu hanya bisa mengedit ulasan milik akunmu sendiri.');
        }

        $data = $request->validate([
            'message' => 'required|string|min:3|max:400',
            'rating' => 'required|integer|min:1|max:5',
        ]);

        $testimonial->forceFill([
            'message' => $data['message'],
            'rating' => (int) $data['rating'],
            'name' => $user->name,
            'is_edited' => 1,
        ])->save();

        return back()->with('success', 'Ulasan website kamu berhasil diedit.');
    }

    public function destroy(Testimonial $testimonial)
    {
        $user = $this->authUser();
        if (!$user) {
            return redirect()->route('login')->withErrors(['login' => 'Silakan login dulu untuk menghapus ulasan website.']);
        }

        if ((int) $testimonial->user_id !== (int) $user->id) {
            abort(403, 'Kamu hanya bisa menghapus ulasan milik akunmu sendiri.');
        }

        DB::transaction(function () use ($testimonial) {
            TestimonialLike::where('testimonial_id', $testimonial->id)->delete();
            $testimonial->delete();
        });

        return back()->with('success', 'Ulasan website kamu berhasil dihapus.');
    }

    public function toggleLike(Testimonial $testimonial)
    {
        $user = $this->authUser();
        if (!$user) {
            return response()->json([
                'ok' => false,
                'message' => 'Silakan login dulu untuk like komentar.',
                'redirect' => route('login'),
            ], 401);
        }

        $liked = false;
        $count = DB::transaction(function () use ($testimonial, $user, &$liked) {
            $existing = TestimonialLike::where('testimonial_id', $testimonial->id)
                ->where('user_id', $user->id)
                ->first();

            $storedCount = (int) ($testimonial->likes_count ?? 0);
            $realCount = TestimonialLike::where('testimonial_id', $testimonial->id)->count();
            $baseCount = max($storedCount, $realCount);

            if ($existing) {
                $existing->delete();
                $liked = false;
                $count = max(0, $baseCount - 1);
            } else {
                TestimonialLike::create([
                    'testimonial_id' => $testimonial->id,
                    'user_id' => $user->id,
                ]);
                $liked = true;
                $count = $baseCount + 1;
            }

            $testimonial->forceFill(['likes_count' => $count])->save();

            return $count;
        });

        return response()->json([
            'ok' => true,
            'liked' => $liked,
            'likes_count' => $count,
        ]);
    }
}
