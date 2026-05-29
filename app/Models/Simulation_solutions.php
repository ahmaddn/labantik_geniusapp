<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Simulation_solutions extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'mission_id',
        'instruction',
        'result_6_months_correct',
        'result_6_months_incorrect',
        'final_feedback',
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
        return $this->hasMany(Simulation_solution_options::class, 'simulation_solution_id');
    }
}
