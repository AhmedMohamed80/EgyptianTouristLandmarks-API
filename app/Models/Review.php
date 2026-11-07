<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;
    protected $primaryKey = 'Rev_ID';
    protected $table = 'reviews';
    protected $fillable = ['id', 'Item_ID', 'content', 'date', 'rating_value'];
    public $timestamps = false;

    public function item()
    {
        return $this->belongsTo(Item::class, 'Item_ID');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id');
    }
    public function reviews()
{
    return $this->hasManyThrough(Review::class, Item::class, 'landmark_id', 'Item_ID', 'id', 'id');
}

}
