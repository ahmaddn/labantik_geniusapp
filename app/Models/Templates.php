<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
