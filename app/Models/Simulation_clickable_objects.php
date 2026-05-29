<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Simulation_clickable_objects extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'mission_id',
        'name',
        'image',
        'pos_x',
        'pos_y',
        'impact_text',
        'is_positive',
    ];

    protected $casts = [
        'is_positive' => 'boolean',
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
