<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Learning_modules extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'template_id',
        'name',
        'description',
        'content',
        'quotes',
        'closing_text',
        'is_active',
        'thumbnail',
        'created_by',
    ];

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid(); // ← cast ke string
            }
        });

        static::deleting(function ($model) {
            // Delete missions (which will delete their materials and quizzes)
            if (method_exists($model, 'missions')) {
                foreach ($model->missions as $mission) {
                    $mission->delete();
                }
            }

            // Delete remaining materials directly associated to module
            if (method_exists($model, 'materials')) {
                foreach ($model->materials as $mat) {
                    $mat->delete();
                }
            }

            // Delete module-level quizzes (mission-less)
            if (method_exists($model, 'quizzes')) {
                foreach ($model->quizzes as $quiz) {
                    $quiz->delete();
                }
            }

            // Finally remove thumbnail file
            if (!empty($model->thumbnail)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($model->thumbnail);
            }
        });
    }

    public function missions()
    {
        return $this->hasMany(Missions::class, 'module_id');
    }

    public function materials()
    {
        return $this->hasMany(\App\Models\Materials::class, 'module_id');
    }

    public function quizzes()
    {
        return $this->hasMany(\App\Models\Quizzes::class, 'module_id');
    }

    public function template()
    {
        return $this->belongsTo(Templates::class, 'template_id');
    }
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
