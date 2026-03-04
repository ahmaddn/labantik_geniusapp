<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Materials extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'title',
        'description',
        'content',
        'image',
        'thumbnail',
        'created_by',
        'mascot_id',
        'module_id',
        'mission_id',
    ];

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = Str::uuid();
            }
        });
        static::deleting(function ($model) {
            if (!empty($model->image)) {
                Storage::disk('public')->delete($model->image);
            }
            if (!empty($model->thumbnail)) {
                Storage::disk('public')->delete($model->thumbnail);
            }
        });
    }

    public function module()
    {
        return $this->belongsTo(Learning_modules::class, 'module_id');
    }
    public function mascot()
    {
        return $this->belongsTo(Mascots::class, 'mascot_id');
    }
    public function mission()
    {
        return $this->belongsTo(Missions::class, 'mission_id');
    }
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
