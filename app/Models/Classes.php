<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Classes extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = [
        'name',
        'description',
        'teacher_id',
    ];

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid(); // ← cast ke string
            }
        });
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'class_id');
    }
}
