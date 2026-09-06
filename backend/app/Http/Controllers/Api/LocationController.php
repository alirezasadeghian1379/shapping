<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;use Illuminate\Http\Request;use Illuminate\Support\Facades\DB;
class LocationController extends Controller{public function provinces(){return DB::table('provinces')->where('is_active',true)->orderBy('sort_order')->orderBy('name')->get(['id','name','slug']);}public function cities(Request $r){$data=$r->validate(['province_id'=>'required|integer|exists:provinces,id']);return DB::table('cities')->where('province_id',$data['province_id'])->where('is_active',true)->orderBy('sort_order')->orderBy('name')->get(['id','province_id','name','slug']);}}
