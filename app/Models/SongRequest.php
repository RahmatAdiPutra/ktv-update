<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SongRequest extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'title',
        'artist',
        'processed'
    ];
}
