<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;
    protected $table = 'items';
    protected $primaryKey = 'Item_ID';
    
    protected $keyType = 'int';

    protected $fillable = ['name', 'description', 'city', 'location', 'image_path'];

    public function photos()
    {
        return $this->hasMany(Photo::class, 'Item_ID');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'Item_ID');
    }

    public function owner()
    {
        return $this->hasOne(Owns::class, 'Item_ID');
    }

    public function hotel()
    {
        return $this->hasOne(Hotel::class, 'Item_ID');
    }

    public function landmark()
    {
        return $this->hasOne(Landmark::class, 'Item_ID');
    }

    public function airport()
    {
        return $this->hasOne(Airport::class, 'Item_ID');
    }

    public function restaurant()
    {
        return $this->hasOne(Restaurant::class, 'Item_ID');
    }

    public function shoppingCenter()
    {
        return $this->hasOne(ShoppingCenter::class, 'Item_ID');
    }
}


