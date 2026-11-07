<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('landmarks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('Item_ID'); // يجب أن يكون نفس نوع id في items
            $table->foreign('Item_ID')->references('Item_ID')->on('items')->onDelete('cascade');
         
            $table->enum('type', ['Pharaonic', 'Islamic', 'Christian']);
            $table->time('open_time');
            $table->time('close_time');
    
            // أسعار التذاكر
            $table->string('egyptian_adult_price', 50);
            $table->string('egyptian_kid_price', 50);
            $table->string('egyptian_student_price', 50);
            $table->string('foreigner_adult_price', 50);
            $table->string('foreigner_kid_price', 50);
            $table->string('foreigner_student_price', 50);
    
            $table->timestamps();
        });
    }
    

    public function down()
    {
        Schema::dropIfExists('landmarks');
    }
};

