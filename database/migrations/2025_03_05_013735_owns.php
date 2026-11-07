<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        
    Schema::create('owns', function (Blueprint $table) {
        $table->unsignedBigInteger('id'); // يجب أن يكون نفس نوع id في users
        $table->unsignedBigInteger('Item_ID'); // يجب أن يكون نفس نوع id في items
        $table->timestamps();

        // تعريف العلاقات بشكل صحيح
        $table->foreign('id')->references('id')->on('users')->onDelete('cascade');
        $table->foreign('Item_ID')->references('Item_ID')->on('items')->onDelete('cascade');
    
        });
    }

    public function down()
    {
        Schema::dropIfExists('owns');
    }
};

