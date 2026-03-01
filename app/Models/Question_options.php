<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Question_options extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'question_id',
        'option_text',
        'is_correct',
        'feedback',
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
}
