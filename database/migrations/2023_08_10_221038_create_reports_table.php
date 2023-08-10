<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cat_id');
            $table->foreign('cat_id')->references('id')->on('categories')->onDelete('cascade')->onUpdate('cascade');
            $table->unsignedBigInteger('reported_by');
            $table->foreign('reported_by')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->string('location');
            $table->string('lat');
            $table->string('long');
            $table->string('photo');
            $table->longText('description');
            $table->unsignedBigInteger('status');
            $table->foreign('status')->references('id')->on('ref_report_status')->onDelete('cascade')->onUpdate('cascade');            
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
        Schema::dropIfExists('reports');
    }
}
