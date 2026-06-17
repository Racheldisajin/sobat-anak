<?php
namespace App\Http\Controllers;
use App\Models\{CartItem,Product,Reward,UserPoint,RewardClaim};
use Illuminate\Http\Request;

class UserActionController extends Controller
{
    private function userId(){ return session('user_id'); }
    private function requireLogin(){ if(!$this->userId()) abort(response()->json(['ok'=>false,'message'=>'Silakan login dulu agar cart dan poin tersimpan per akun.','redirect'=>route('login')], 401)); }

    public function addCart(Request $request){
        $this->requireLogin();
        $data = $request->validate(['product_id'=>'required|integer|exists:products,id']);
        $product = Product::findOrFail($data['product_id']);
        $stock = (int) ($product->stock ?? 0);

        if ($stock <= 0) {
            return response()->json(['ok'=>false,'message'=>'Maaf, stok produk ini sedang habis.'], 422);
        }

        $item = CartItem::firstOrNew(['user_id'=>$this->userId(),'product_id'=>$data['product_id']]);
        $nextQty = ($item->quantity ?: 0) + 1;
        if ($nextQty > $stock) {
            return response()->json(['ok'=>false,'message'=>'Jumlah produk di cart sudah mencapai stok tersedia.'], 422);
        }
        $item->quantity = $nextQty;
        $item->save();
        $count = CartItem::where('user_id',$this->userId())->sum('quantity');
        return response()->json(['ok'=>true,'cart_count'=>$count,'message'=>'Produk berhasil masuk keranjang akun kamu.']);
    }

    public function playGame(){
        $this->requireLogin();
        $win = random_int(20, 80);
        $point = UserPoint::firstOrCreate(['user_id'=>$this->userId()],['points'=>1250]);
        $point->points += $win;
        $point->save();
        return response()->json(['ok'=>true,'points'=>$point->points,'earned'=>$win,'message'=>'Yeay! Kamu dapat '.$win.' poin 🎉']);
    }

    public function redeem(Request $request){
        $this->requireLogin();
        $data = $request->validate(['reward_id'=>'required|integer|exists:rewards,id']);
        $reward = Reward::findOrFail($data['reward_id']);
        $point = UserPoint::firstOrCreate(['user_id'=>$this->userId()],['points'=>1250]);

        if($point->points < $reward->points){
            if($request->expectsJson()){
                return response()->json(['ok'=>false,'points'=>$point->points,'message'=>'Poin belum cukup untuk menukar reward ini.'], 422);
            }
            return back()->withErrors(['reward'=>'Poin belum cukup untuk menukar reward ini.']);
        }

        $point->points -= $reward->points;
        $point->save();

        RewardClaim::create([
            'user_id'=>$this->userId(),
            'reward_name'=>$reward->name,
            'points_used'=>$reward->points
        ]);

        if($request->expectsJson()){
            return response()->json(['ok'=>true,'points'=>$point->points,'message'=>'Reward berhasil ditukar dan tersimpan di profil.']);
        }

        return redirect()->route('profile.rewards')->with('success','Reward berhasil ditukar dan tersimpan di profile.');
    }
}