<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Drag_drop_groups extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'question_id',
        'group_name',
    ];

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = Str::uuid();
            }
        });

        // Auto delete related items when group is deleted
        static::deleting(function ($model) {
            $model->items()->delete();
        });
    }

    public function question()
    {
        return $this->belongsTo(Questions::class, 'question_id');
    }

    public function items()
    {
        return $this->hasMany(Drag_drop_items::class, 'drag_drop_group_id');
    }
}
