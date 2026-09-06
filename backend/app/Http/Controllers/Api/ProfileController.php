<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller; use App\Models\Order; use Illuminate\Http\Request;
class ProfileController extends Controller {
 public function dashboard(Request $r){$u=$r->user();return ['user'=>$u,'orders_count'=>Order::where('user_id',$u->id)->count(),'pending_orders'=>Order::where('user_id',$u->id)->whereNotIn('status',['delivered','cancelled','returned'])->count(),'favorites_count'=>$u->belongsToMany(\App\Models\Product::class,'favorites')->count(),'saved_articles_count'=>$u->belongsToMany(\App\Models\Article::class,'saved_articles')->count()];}
 public function orders(Request $r){return Order::where('user_id',$r->user()->id)->withCount('items')->latest()->paginate(15);}
 public function order(Request $r,Order $order){abort_unless($order->user_id===$r->user()->id,404);return $order->load(['items','payments','shipment.events'])->loadCount(['returnRequests as active_returns_count'=>fn($q)=>$q->whereNotIn('status',['rejected','cancelled'])]);}
 public function invoice(Request $r,Order $order){abort_unless($order->user_id===$r->user()->id,404);return ['order'=>$order->load(['items','user','shipment']),'settings'=>\App\Models\Setting::where('is_public',true)->pluck('value','key')];}
}
