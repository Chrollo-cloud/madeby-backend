<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Listing extends Model
{
    use HasFactory;

    protected $fillable = ['slug', 'type', 'title', 'category_id', 'creator_id', 'price', 'price_from', 'stock', 'commission_slots', 'delivery_time', 'rating', 'description', 'image', 'tags'];

    protected $casts = ['tags' => 'array', 'rating' => 'decimal:1'];

    public function creator()
    {
        return $this->belongsTo(Creator::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function images()
    {
        return $this->hasMany(ListingImage::class);
    }
}
