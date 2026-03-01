<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Questions extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'quiz_id',
        'mascot_id',
        'question_text',
        'image',
        'order_number',
    ];

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = Str::uuid();
            }
        });

        // Auto delete related records when question is deleted
        static::deleting(function ($model) {
            $model->options()->delete();
            $model->dragDropGroups()->delete();
            $model->dragDropItems()->delete();
        });
    }

    public function quiz()
    {
        return $this->belongsTo(Quizzes::class, 'quiz_id');
    }

    public function mascot()
    {
        return $this->belongsTo(Mascots::class, 'mascot_id');
    }

    public function options()
    {
        return $this->hasMany(Question_options::class, 'question_id');
    }

    public function dragDropGroups()
    {
        return $this->hasMany(Drag_drop_groups::class, 'question_id');
    }

    public function dragDropItems()
    {
        return $this->hasMany(Drag_drop_items::class, 'question_id');
    }
}
