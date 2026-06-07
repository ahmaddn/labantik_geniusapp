<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Simulation_comparisons extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'mission_id',
        'title',
        'items',
        'explanation',
        'order_number',
    ];

    protected $casts = [
        'items' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    public function mission()
    {
        return $this->belongsTo(Missions::class, 'mission_id');
    }
}
