<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Quiz_attempts extends Model
{
    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'quiz_id',
        'student_id',
        'score',
        'started_at',
        'finished_at',
    ];

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid(); // ← cast ke string
            }
        });
    }

    public function quiz()
    {
        return $this->belongsTo(Quizzes::class, 'quiz_id');
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function answers()
    {
        return $this->hasMany(User_answers::class, 'attempt_id');
    }
}
