<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model; use Illuminate\Database\Eloquent\SoftDeletes;
class Article extends Model {use SoftDeletes;protected $guarded=[];protected function casts():array{return ['tags'=>'array','seo'=>'array','published_at'=>'datetime'];}public function comments(){return $this->morphMany(Comment::class,'commentable');}}
