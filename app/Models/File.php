<?php

namespace App\Models;

use App\Traits\hasCreatorAndUpdator;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Kalnoy\Nestedset\NodeTrait;
use Illuminate\Support\Str;

class File extends Model
{
    use HasFactory, hasCreatorAndUpdator, NodeTrait, SoftDeletes;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }


    public function get_file_size()
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $power = $this->size > 0 ? floor(log($this->size, 1024)) : 0;

        return number_format($this->size / pow(1024, $power), 2, '.', ',') . ' ' . $units[$power];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(File::class, 'parent_id');
    }

    public function owner(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes) {
                return $attributes['created_by'] == Auth::id() ? 'me' : $this->user->name;
            }
        );
    }

    public function isRoot(): bool
    {
        return $this->parent_id === null;
    }
    public function isOwnedBy($userId): bool
    {
        return $this->created_by == $userId;
    }

    public static function DeleteFolder(File $parent)
    {
        $children = $parent->children();
        foreach ($children as $child) {
            if ($parent->is_folder) {
                self::DeleteFolder($child);
            } else {
                Storage::delete($child->storage_path);
            }
        }
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (! $model->parent) {
                return;
            }
            $model->path = (!$model->parent->isRoot() ? $model->parent->path . '/' : '') . Str::slug($model->name);
        });



        // static::deleted(function (File $model) {
        //     if (!$model->is_folder) {
        //         Storage::delete($model->storage_path);
        //     } else {
        //         self::DeleteFolder($model);
        //     }
        // });
    }
}
