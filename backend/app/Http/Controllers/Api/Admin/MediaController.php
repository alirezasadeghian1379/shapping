<?php
namespace App\Http\Controllers\Api\Admin;
use App\Http\Controllers\Controller; use Illuminate\Http\Request; use Illuminate\Support\Facades\Storage;
class MediaController extends Controller {
 public function store(Request $request) { $data=$request->validate(['file'=>['required','file','mimes:jpg,jpeg,png,webp,gif,svg','max:5120'],'directory'=>['nullable','in:products,articles,banners,sliders,branding']]); $path=$data['file']->store(($data['directory']??'uploads').'/'.now()->format('Y/m'),'public'); return response()->json(['path'=>$path,'url'=>Storage::disk('public')->url($path),'name'=>$data['file']->getClientOriginalName()],201); }
 public function destroy(Request $request) { $data=$request->validate(['path'=>['required','string','max:500']]); abort_if(str_contains($data['path'],'..'),422,'مسیر فایل معتبر نیست.'); Storage::disk('public')->delete($data['path']); return response()->noContent(); }
}
