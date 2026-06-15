<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Simulation_slider_levels extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'simulation_slider_id',
        'level_name',
        'narration',
        'metric_value',
        'image',
    ];

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    public function slider()
    {
        return $this->belongsTo(Simulation_sliders::class, 'simulation_slider_id');
    }
}
