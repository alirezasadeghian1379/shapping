<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller; use App\Models\Article; use App\Models\Product; use Illuminate\Http\Request;
class ContentController extends Controller {
 public function articles(Request $r){return Article::where('status','published')->whereNotNull('published_at')->latest('published_at')->paginate(min($r->integer('per_page',12),30));}
 public function article(string $slug){$a=Article::where('slug',$slug)->where('status','published')->with(['comments'=>fn($q)=>$q->where('status','approved')->with('user:id,name,avatar')->latest()])->firstOrFail();$a->increment('view_count');return $a;}
 public function productComments(Product $product){$base=$product->comments()->whereNull('parent_id')->where('status','approved');$total=(clone $base)->whereNotNull('rating')->count();$average=(float)((clone $base)->whereNotNull('rating')->avg('rating')??0);$distribution=collect(range(5,1))->mapWithKeys(fn($rating)=>[$rating=>(clone $base)->where('rating',$rating)->count()]);$comments=$base->with(['user:id,name,avatar','replies'=>fn($q)=>$q->where('status','approved')->with('user:id,name,avatar')->oldest()])->withCount('votes')->latest()->paginate(15);return ['summary'=>['average'=>round($average,1),'ratings_count'=>$total,'distribution'=>$distribution],'comments'=>$comments];}
 public function compare(Request $r){$ids=collect(explode(',',(string)$r->query('ids')))->filter()->unique()->take(4);abort_if($ids->count()<2,422,'حداقل دو محصول برای مقایسه انتخاب کنید.');return Product::whereIn('id',$ids)->where('status','published')->with(['category','brand','variants','media'])->get();}
}
