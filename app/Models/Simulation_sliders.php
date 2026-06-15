<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Simulation_sliders extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'mission_id',
        'title',
        'x_axis_label',
        'conclusion_text',
        'order_number',
        'variables',
    ];

    protected $casts = [
        'variables' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
        
        static::deleting(function ($model) {
            $model->levels()->delete();
        });
    }

    public function mission()
    {
        return $this->belongsTo(Missions::class, 'mission_id');
    }

    public function levels()
    {
        return $this->hasMany(Simulation_slider_levels::class, 'simulation_slider_id');
    }
}
