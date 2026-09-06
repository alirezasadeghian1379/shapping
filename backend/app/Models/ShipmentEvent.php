<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ShipmentEvent extends Model { protected $guarded=[]; protected function casts():array{return ['occurred_at'=>'datetime'];} }
