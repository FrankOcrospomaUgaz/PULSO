<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAmenitiesTable extends Migration
{
    public function up()
    {
        Schema::create('amenities', function (Blueprint $table) {
            $table->id();
            $table->string('nombre'); 
            $table->text('icon'); 
            $table->unsignedBigInteger('tipohabitacion_id');

            $table->timestamps();
            $table->softDeletes();
            $table->foreign('tipohabitacion_id')
                ->references('id')
                ->on('tipohabitacion') // plural correcto según convención Laravel
                ->onDelete('cascade');
            
        });
    }

    public function down()
    {
        Schema::dropIfExists('amenities');
    }
}
