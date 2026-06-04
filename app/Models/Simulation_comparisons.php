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
        'left_label',
        'right_label',
        'left_narration',
        'right_narration',
        'left_image',
        'right_image',
        'explanation',
        'order_number',
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
