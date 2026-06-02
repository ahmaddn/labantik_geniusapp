<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Simulation_scenarios extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'module_id',
        'mission_id',
        'context',
        'image',
        'correct_option',
    ];

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
        
        static::deleting(function ($model) {
            $model->options()->delete();
        });
    }

    public function mission()
    {
        return $this->belongsTo(Missions::class, 'mission_id');
    }

    public function options()
    {
        return $this->hasMany(Simulation_scenario_options::class, 'simulation_scenario_id');
    }

    public function module()
    {
        return $this->belongsTo(Learning_modules::class, 'module_id');
    }
}
