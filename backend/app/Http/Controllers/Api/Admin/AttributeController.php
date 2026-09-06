<?php
namespace App\Http\Controllers\Api\Admin;
use App\Http\Controllers\Controller;use Illuminate\Http\Request;use Illuminate\Support\Facades\DB;use Illuminate\Validation\Rule;
class AttributeController extends Controller{
 public function index(){return DB::table('attributes')->orderBy('id')->get()->map(function($a){$a->values=DB::table('attribute_values')->where('attribute_id',$a->id)->orderBy('sort_order')->get();return $a;});}
 public function store(Request $r){$d=$this->data($r);$id=DB::table('attributes')->insertGetId($d+['created_at'=>now(),'updated_at'=>now()]);return response()->json(DB::table('attributes')->find($id),201);}
 public function update(Request $r,int $attribute){abort_unless(DB::table('attributes')->where('id',$attribute)->exists(),404);DB::table('attributes')->where('id',$attribute)->update($this->data($r,$attribute)+['updated_at'=>now()]);return DB::table('attributes')->find($attribute);}
 public function destroy(int $attribute){DB::table('attributes')->where('id',$attribute)->delete();return response()->noContent();}
 public function storeValue(Request $r,int $attribute){abort_unless(DB::table('attributes')->where('id',$attribute)->exists(),404);$d=$this->valueData($r,$attribute);$id=DB::table('attribute_values')->insertGetId($d+['attribute_id'=>$attribute,'created_at'=>now(),'updated_at'=>now()]);return response()->json(DB::table('attribute_values')->find($id),201);}
 public function updateValue(Request $r,int $value){$row=DB::table('attribute_values')->find($value);abort_unless($row,404);DB::table('attribute_values')->where('id',$value)->update($this->valueData($r,$row->attribute_id,$value)+['updated_at'=>now()]);return DB::table('attribute_values')->find($value);}
 public function destroyValue(int $value){DB::table('attribute_values')->where('id',$value)->delete();return response()->noContent();}
 private function data(Request $r,?int $id=null):array{return $r->validate(['title'=>'required|string|max:100','slug'=>['required','alpha_dash',Rule::unique('attributes')->ignore($id)],'type'=>'required|in:select,color,text,number','is_filterable'=>'sometimes|boolean','is_variant'=>'sometimes|boolean','unit'=>'nullable|string|max:30']);}
 private function valueData(Request $r,int $attribute,?int $id=null):array{return $r->validate(['value'=>['required','string','max:100',Rule::unique('attribute_values')->where(fn($q)=>$q->where('attribute_id',$attribute))->ignore($id)],'label'=>'required|string|max:100','color'=>'nullable|regex:/^#[0-9a-fA-F]{6}$/','sort_order'=>'sometimes|integer|min:0']);}
}
