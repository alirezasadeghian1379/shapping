<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller; use App\Models\CartItem; use App\Models\ProductVariant; use Illuminate\Http\Request; use Illuminate\Validation\ValidationException; use App\Services\Catalog\PricingService;
class CartController extends Controller {
 public function __construct(private PricingService $pricing){}
 public function index(Request $request){$items=CartItem::where('user_id',$request->user()->id)->with('variant.product.media')->get();return $this->summary($items);}
 public function store(Request $request){$data=$request->validate(['variant_id'=>'required|exists:product_variants,id','quantity'=>'sometimes|integer|min:1|max:20']);$variant=ProductVariant::with('product')->where('is_active',true)->findOrFail($data['variant_id']);abort_unless($variant->product->status==='published',422,'این محصول قابل سفارش نیست.');$item=CartItem::firstOrNew(['user_id'=>$request->user()->id,'variant_id'=>$variant->id]);$item->quantity=min(($item->exists?$item->quantity:0)+($data['quantity']??1),20);if($item->quantity>$variant->stock)throw ValidationException::withMessages(['quantity'=>'موجودی این تنوع کافی نیست.']);$item->save();return $this->index($request);}
 public function update(Request $request,CartItem $cartItem){abort_unless($cartItem->user_id===$request->user()->id,404);$data=$request->validate(['quantity'=>'required|integer|min:1|max:20']);$variant=$cartItem->variant;if($data['quantity']>$variant->stock)throw ValidationException::withMessages(['quantity'=>'موجودی این تنوع کافی نیست.']);$cartItem->update($data);return $this->index($request);}
 public function destroy(Request $request,CartItem $cartItem){abort_unless($cartItem->user_id===$request->user()->id,404);$cartItem->delete();return $this->index($request);}
 private function summary($items):array{$items->each(function($x){$x->variant->effective_price=$this->pricing->price($x->variant);});$subtotal=$items->sum(fn($x)=>$x->variant->effective_price*$x->quantity);return ['items'=>$items,'count'=>$items->sum('quantity'),'subtotal'=>$subtotal];}
}
