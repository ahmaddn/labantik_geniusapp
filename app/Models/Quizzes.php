<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Quizzes extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'module_id',
        'mission_id', // Nullable jika ini kuis tingkat modul
        'title',
        'description',
        'image',
        'type', // 'pretest' atau 'posttest' atau 'general'
        'duration_minutes',
        'created_by',
        'time_limit',
        'category',
    ];

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid(); // ← cast ke string
            }
        });

        // Auto delete related records when quiz is deleted
        // Use model deletes to ensure child model deleting events run
        static::deleting(function ($model) {
            // Delete quiz cover image if present
            if (!empty($model->image)) {
                Storage::disk('public')->delete($model->image);
            }

            if (method_exists($model, 'attempts')) {
                foreach ($model->attempts as $attempt) {
                    $attempt->delete();
                }
            }

            foreach ($model->questions as $question) {
                $question->delete();
            }
        });
    }

    public function module()
    {
        return $this->belongsTo(Learning_modules::class, 'module_id');
    }

    public function mission()
    {
        return $this->belongsTo(Missions::class, 'mission_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function questions()
    {
        return $this->hasMany(Questions::class, 'quiz_id');
    }

    public function attempts()
    {
        return $this->hasMany(Quiz_attempts::class, 'quiz_id');
    }
}
