<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Refund extends Model { protected $guarded=[]; protected function casts():array{return ['response_payload'=>'array','processed_at'=>'datetime'];} public function payment(){return $this->belongsTo(Payment::class);} }
