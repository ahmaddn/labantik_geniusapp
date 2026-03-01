<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Learning_modules extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'template_id',
        'name',
        'description',
        'content',
        'quotes',
        'is_active',
        'thumbnail',
        'created_by',
    ];

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = Str::uuid();
            }
        });
    }

    public function template()
    {
        return $this->belongsTo(Templates::class, 'template_id');
    }
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
