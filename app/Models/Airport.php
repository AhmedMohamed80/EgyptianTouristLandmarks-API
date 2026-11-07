<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Airport extends Model
{
    use HasFactory;

    protected $fillable = ['Item_ID','website_link','phone'];


    public $timestamps = false;
    public function item()
    {
        return $this->belongsTo(Item::class, 'Item_ID', 'Item_ID');
    }

    public function reviews()
    {
        return $this->hasManyThrough(
            Review::class, 
            Item::class,   
            'Item_ID',     
            'Item_ID',     
            'Item_ID',     
            'Item_ID'      
        );
    }

    public function  facilities_airports()
    {
        return $this->hasMany(facilities_airports::class);
    }
        public function  Airlines()
    {
        return $this->hasMany(Airlines::class);
    }
}
