<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ArtistCategory extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name'
    ];

    public function artists()
    {
		return $this->hasMany(Artist::class, 'artist_categories_id');
	}
}
