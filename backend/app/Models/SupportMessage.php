<?php
namespace App\Models;use Illuminate\Database\Eloquent\Model;
class SupportMessage extends Model{protected $guarded=[];protected function casts():array{return ['attachments'=>'array','is_staff'=>'boolean','read_at'=>'datetime'];}public function user(){return $this->belongsTo(User::class);}}
