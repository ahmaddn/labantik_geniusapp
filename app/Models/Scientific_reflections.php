<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Scientific_reflections extends Model
{
    use HasFactory;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'mission_id',
        'title',
        'mascot_left_text',
        'mascot_right_text',
        'flowchart_data',
        'order_number',
    ];

    protected $casts = [
        'flowchart_data' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });

        static::deleting(function ($model) {
            if (method_exists($model, 'questions')) {
                foreach ($model->questions as $question) {
                    $question->delete();
                }
            }
        });
    }

    public function mission()
    {
        return $this->belongsTo(Missions::class, 'mission_id');
    }

    public function questions()
    {
        return $this->hasMany(Reflection_questions::class, 'reflection_id')->orderBy('order_number');
    }
}
