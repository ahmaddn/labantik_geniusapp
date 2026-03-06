<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Drag_drop_items extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'question_id',
        'drag_drop_group_id',
        'item_text',
        'item_image',
    ];

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid(); // ← cast ke string
            }
        });
    }

    public function questions()
    {
        return $this->belongsTo(Questions::class, 'question_id');
    }
    public function dragDropGroups()
    {
        return $this->belongsTo(Drag_drop_groups::class, 'drag_drop_group_id');
    }
}
