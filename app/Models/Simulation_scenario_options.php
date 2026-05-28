<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Simulation_scenario_options extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'simulation_scenario_id',
        'label',
        'text',
        'feedback',
    ];

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    public function scenario()
    {
        return $this->belongsTo(Simulation_scenarios::class, 'simulation_scenario_id');
    }
}
