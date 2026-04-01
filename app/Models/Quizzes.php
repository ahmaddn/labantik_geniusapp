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
        'title',
        'description',
        'time_limit',
        'type',
        'category',
        'image',
        'created_by',
        'module_id',
        'mission_id',
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
}
