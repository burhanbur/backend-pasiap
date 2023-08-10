<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('profiles', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->primary();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->string('sid', 16)->unique();
            $table->string('email')->unique();
            $table->string('full_name');
            $table->string('birth_place')->nullable();
            $table->string('birth_date')->nullable();
            $table->string('sex')->nullable();
            $table->unsignedBigInteger('religion')->nullable();
            $table->foreign('religion')->references('id')->on('ref_religions')->onDelete('cascade')->onUpdate('cascade');
            $table->string('martial_status')->nullable();
            $table->string('phone');
            $table->string('identity_card_photo')->nullable();
            $table->string('photo')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('profiles');
    }
}
