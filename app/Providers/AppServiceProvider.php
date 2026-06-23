<?php
namespace App\Providers;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\{User,UserPoint,CartItem,Product};

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}
    public function boot(): void
    {
        View::composer('*', function($view){
            $user = null; $points = 0; $cartCount = 0;
            if(session('user_id')){
                $user = User::find(session('user_id'));
                if($user){
                    $points = UserPoint::firstOrCreate(['user_id'=>$user->id],['points'=>0])->points;
                    $cartCount = CartItem::where('user_id',$user->id)->sum('quantity');
                }
            }
            $searchProducts = Product::select('id','name','category','price','image','rating','sold','stock')
                ->orderByDesc('rating')
                ->orderByDesc('sold')
                ->limit(12)
                ->get();

            $view->with('authUser',$user)
                ->with('authPoints',$points)
                ->with('authCartCount',$cartCount)
                ->with('searchProducts',$searchProducts);
        });
    }
}
