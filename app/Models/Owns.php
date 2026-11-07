<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Owns extends Model
{
    use HasFactory;

    protected $fillable = ['id', 'Item_ID'];

    public function item()
    {
        return $this->belongsTo(Item::class, 'Item_ID');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id');
    }
}
