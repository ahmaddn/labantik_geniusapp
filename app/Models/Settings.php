<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Settings extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'platform_name',
        'platform_subtitle',
        'platform_logo',
        'platform_mascot',
        'platform_mascot_pose',
        'platform_mascot_dialog',
        'bgm_file',
        'bgm_enabled',
    ];

    protected $casts = [
        'platform_mascot_dialog' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });

        static::deleting(function ($model) {
            if (!empty($model->platform_logo)) {
                Storage::disk('public')->delete($model->platform_logo);
            }
            if (!empty($model->platform_mascot)) {
                Storage::disk('public')->delete($model->platform_mascot);
            }
            if (!empty($model->bgm_file)) {
                Storage::disk('public')->delete($model->bgm_file);
            }
        });
    }
}
