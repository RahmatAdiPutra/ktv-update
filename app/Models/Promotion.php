<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    public $timestamps = false;
    
    protected $fillable = [
        'name',
        'src',
        'start_date',
        'end_date'
    ];
}
