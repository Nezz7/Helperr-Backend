<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHelpSessionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('help_sessions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->string('helper_name');
            $table->string('helpee_name');

            $table->bigInteger('request_id');
            $table->bigInteger('helper_id');
            $table->bigInteger('helpee_id');
            
            $table->text('helper_review')->nullable();
            $table->text('helpee_review')->nullable();
            $table->integer('helper_score')->nullable();
            $table->integer('helpee_score')->nullable();
            $table->enum("status",["pending","canceled","TLE","failed","review","succeeded","submitted"])->default("pending");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('help_sessions');
    }
}
