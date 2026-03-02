<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class User_answers extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'attempt_id',
        'question_id',
        'selected_option_id',
        'selected_group_id',
        'response',
    ];

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = Str::uuid();
            }
        });
    }

    public function question()
    {
        return $this->belongsTo(Questions::class, 'question_id');
    }
    public function attempt()
    {
        return $this->belongsTo(Quiz_attempts::class, 'attempt_id');
    }
    public function selectedOption()
    {
        return $this->belongsTo(Question_options::class, 'selected_option_id');
    }
    public function selectedGroup()
    {
        return $this->belongsTo(Drag_drop_groups::class, 'selected_group_id');
    }
}
