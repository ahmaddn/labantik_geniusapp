<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Simulation_solution_options extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'simulation_solution_id',
        'label',
        'image',
        'correctness',
    ];

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    public function solution()
    {
        return $this->belongsTo(Simulation_solutions::class, 'simulation_solution_id');
    }
}
