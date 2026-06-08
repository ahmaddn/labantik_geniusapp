<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Simulation_decision_options extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'simulation_decision_id',
        'button_label',
        'button_color',
        'future_state_image',
        'feedback_message',
    ];

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    public function decision()
    {
        return $this->belongsTo(Simulation_decisions::class, 'simulation_decision_id');
    }
}
