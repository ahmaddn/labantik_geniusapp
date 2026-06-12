<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Simulation_decisions extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'mission_id',
        'title',
        'initial_state_title',
        'initial_state_image',
        'future_state_title',
        'character_image',
        'order_number',
    ];

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });

        static::deleting(function ($model) {
            foreach ($model->options as $opt) {
                if (!empty($opt->future_state_image)) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($opt->future_state_image);
                }
                $opt->delete();
            }
            if (!empty($model->initial_state_image)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($model->initial_state_image);
            }
            if (!empty($model->character_image)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($model->character_image);
            }
        });
    }

    public function mission()
    {
        return $this->belongsTo(Missions::class, 'mission_id');
    }

    public function options()
    {
        return $this->hasMany(Simulation_decision_options::class, 'simulation_decision_id');
    }
}
