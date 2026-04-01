<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
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
                $model->id = (string) Str::uuid(); // ← cast ke string
            }
        });

        // Auto delete related records and files when question is deleted
        static::deleting(function ($model) {
            // Delete question image if present
            if (!empty($model->image)) {
                Storage::disk('public')->delete($model->image);
            }

            // Delete options and their images
            foreach ($model->options as $opt) {
                if (!empty($opt->option_image)) {
                    Storage::disk('public')->delete($opt->option_image);
                }
                $opt->delete();
            }

            // Delete drag & drop items and their images before groups
            foreach ($model->dragDropItems as $item) {
                if (!empty($item->item_image)) {
                    Storage::disk('public')->delete($item->item_image);
                }
                $item->delete();
            }

            // Finally delete groups
            foreach ($model->dragDropGroups as $group) {
                $group->delete();
            }
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
