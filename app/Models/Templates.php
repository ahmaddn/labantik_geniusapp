<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class Templates extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'name',
        'backsound',
        'created_by'
    ];

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = Str::uuid();
            }
        });

        // When a template is deleted, remove related backgrounds and mascots (and their files)
        static::deleting(function ($model) {
            // Delete backgrounds and their images
            foreach ($model->backgrounds as $bg) {
                if (!empty($bg->image)) {
                    Storage::disk('public')->delete($bg->image);
                }
                $bg->delete();
            }

            // Delete mascots and their images
            foreach ($model->mascots as $m) {
                if (!empty($m->image)) {
                    Storage::disk('public')->delete($m->image);
                }
                $m->delete();
            }

            // Nullify template_id on learning modules to avoid FK constraints
            if (method_exists($model, 'learning_modules')) {
                foreach ($model->learning_modules as $lm) {
                    $lm->template_id = null;
                    $lm->save();
                }
            }
        });
    }

    public function backgrounds()
    {
        return $this->hasMany(Backgrounds::class, 'template_id');
    }

    public function mascots()
    {
        return $this->hasMany(Mascots::class, 'template_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
