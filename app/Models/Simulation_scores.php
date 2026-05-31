<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Simulation_scores extends Model
{
    protected $fillable = [
        'student_id',
        'mission_id',
        'simulation_scenario_id',
        'score',
        'is_correct'
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function mission()
    {
        return $this->belongsTo(Missions::class, 'mission_id');
    }

    public function simulation_scenario()
    {
        return $this->belongsTo(Simulation_scenarios::class, 'simulation_scenario_id');
    }
}
