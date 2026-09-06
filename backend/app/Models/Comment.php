<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model; use Illuminate\Database\Eloquent\SoftDeletes;
class Comment extends Model {use SoftDeletes;protected $guarded=[];protected function casts():array{return ['is_buyer'=>'boolean','is_staff'=>'boolean'];}public function user(){return $this->belongsTo(User::class);}public function commentable(){return $this->morphTo();}public function replies(){return $this->hasMany(self::class,'parent_id');}public function votes(){return $this->belongsToMany(User::class,'comment_votes')->withTimestamps();}}
