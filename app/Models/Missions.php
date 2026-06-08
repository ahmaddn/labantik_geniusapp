<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Missions extends Model
{
    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'module_id',
        'name',
        'description',
        'hint',
        'order_number',
        'objective',
        'content',
        'image',
        'youtube_link',
        'is_active',
        'conclusion_speech',
        'conclusion_body',
    ];

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid(); // ← cast ke string
            }
        });
        static::deleting(function ($model) {
            // Delete materials (and their files)
            foreach ($model->materials as $mat) {
                $mat->delete();
            }

            // Delete quizzes (Quizzes::deleting will handle questions/files)
            foreach ($model->quizzes as $quiz) {
                $quiz->delete();
            }
        });
    }

    public function module()
    {
        return $this->belongsTo(Learning_modules::class, 'module_id');
    }

    public function materials()
    {
        return $this->hasMany(Materials::class, 'mission_id');
    }

    public function quizzes()
    {
        return $this->hasMany(Quizzes::class, 'mission_id');
    }

    public function simulation_sliders()
    {
        return $this->hasMany(Simulation_sliders::class, 'mission_id');
    }

    public function simulation_comparisons()
    {
        return $this->hasMany(Simulation_comparisons::class, 'mission_id');
    }

    public function simulation_clickable_objects()
    {
        return $this->hasMany(Simulation_clickable_objects::class, 'mission_id');
    }

    public function simulation_decisions()
    {
        return $this->hasMany(Simulation_decisions::class, 'mission_id');
    }

}
