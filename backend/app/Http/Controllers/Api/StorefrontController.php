<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller; use App\Models\Product; use App\Models\Setting; use Illuminate\Support\Facades\DB;
class StorefrontController extends Controller {
 public function bootstrap(){return ['settings'=>Setting::where('is_public',true)->get(['group','key','value'])->mapWithKeys(fn($x)=>[$x->key=>$x->value]),'sliders'=>DB::table('sliders')->where('is_active',true)->where(fn($q)=>$q->whereNull('starts_at')->orWhere('starts_at','<=',now()))->where(fn($q)=>$q->whereNull('ends_at')->orWhere('ends_at','>=',now()))->orderBy('sort_order')->get(),'banners'=>DB::table('banners')->where('is_active',true)->orderBy('sort_order')->get()];}
 public function home(){return ['featured'=>Product::where('status','published')->where('is_featured',true)->with(['variants','media'])->latest('published_at')->limit(12)->get(),'latest'=>Product::where('status','published')->with(['variants','media'])->latest('published_at')->limit(12)->get(),'articles'=>DB::table('articles')->where('status','published')->whereNull('deleted_at')->latest('published_at')->limit(6)->get()];}
}
