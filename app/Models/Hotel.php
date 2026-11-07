<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hotel extends Model
{
    use HasFactory;

    protected $fillable = ['Item_ID', 'website_link', 'contact_number','booking_link','email'];


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
    public function  facilities()
    {
        return $this->hasMany(facilities::class);
    }
}
