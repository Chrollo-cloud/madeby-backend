<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Creator extends Model
{
    use HasFactory;

    protected $fillable = ['slug', 'name', 'role', 'bio', 'location', 'avatar', 'followers', 'likes'];

    public function listings()
    {
        return $this->hasMany(Listing::class);
    }
}
