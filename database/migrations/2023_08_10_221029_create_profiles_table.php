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
            $table->string('sid', 16)->unique()->nullable(false);
            $table->string('email')->unique()->nullable(false);
            $table->string('full_name')->nullable(false);
            $table->string('birth_place');
            $table->string('birth_date');
            $table->string('sex');
            $table->unsignedBigInteger('religion');
            $table->foreign('religion')->references('id')->on('ref_religions')->onDelete('cascade')->onUpdate('cascade');
            $table->string('martial_status');
            $table->string('phone')->nullable(false);
            $table->string('identity_card_photo');
            $table->string('photo');
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
