<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class airlines extends Model
{
    use HasFactory;

    protected $fillable = ['airport_id','name','terminal','email','phone'.'website_link'];


    public function Airport()
    {
        return $this->belongsTo(Airport::class);
    }  

}
