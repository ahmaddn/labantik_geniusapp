<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Reflection_questions extends Model
{
    use HasFactory;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'reflection_id',
        'question_text',
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

    public function reflection()
    {
        return $this->belongsTo(Scientific_reflections::class, 'reflection_id');
    }

    public function answers()
    {
        return $this->hasMany(Reflection_answers::class, 'reflection_question_id');
    }
}
