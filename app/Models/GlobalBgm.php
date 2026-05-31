<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

class GlobalBgm extends Model
{
    use HasUuids;
    
    protected $fillable = [
        'name',
        'file_path',
    ];
}
