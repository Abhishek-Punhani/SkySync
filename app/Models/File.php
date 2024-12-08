<?php

namespace App\Models;

use App\Traits\hasCreatorAndUpdator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Kalnoy\Nestedset\NodeTrait;
use Illuminate\Support\Str;
class File extends Model
{
    use HasFactory,hasCreatorAndUpdator,NodeTrait,SoftDeletes;

    public function user() : BelongsTo
    {
        return $this->belongsTo(User::class,'created_by');
    }

      public function parent() : BelongsTo
    {
        return $this->belongsTo(File::class,'parent_id');
    }


    public function isRoot() : bool
    {
        return $this->parent_id === null;
    }
    public function isOwnedBy($userId) : bool 
    {
        return $this->created_by == $userId;
    }

    protected static function boot(){
        parent::boot();

        static::creating(function ($model) {
            if(! $model->parent){
                return;
            }
            $model->path=(!$model->parent->isRoot() ? $model->parent->path . '/':'') . Str::slug($model->name);
        });
    }
}
