<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Missions extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'module_id',
        'name',
        'order_number',
    ];

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = Str::uuid();
            }
        });
    }

    public function module()
    {
        return $this->belongsTo(Learning_modules::class, 'module_id');
    }

    public function materials()
    {
        return $this->hasMany(Materials::class, 'mission_id');
    }

    public function quizzes()
    {
        return $this->hasMany(Quizzes::class, 'mission_id');
    }
}
