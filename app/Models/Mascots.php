<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class Mascots extends Model
{

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name_pose',
        'image',
        'template_id'
    ];

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid(); // ← cast ke string
            }
        });

        static::deleting(function ($model) {
            if (!empty($model->image)) {
                Storage::disk('public')->delete($model->image);
            }
        });
    }
}
