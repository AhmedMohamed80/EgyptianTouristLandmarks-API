<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacilitiesRestaurants extends Model
{
    use HasFactory;

    protected $fillable = ['restaurant_id', 'facility_name'];

    public function Restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }  
}
