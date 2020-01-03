<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHelpMeRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('help_me_requests', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string("title");
            $table->bigInteger("maker_id")->nullable();
            $table->bigInteger("helper_id")->nullable();
            $table->bigInteger("help_session_id")->nullable();
            $table->text("short_description")->nullable();
            $table->text("description")->nullable();
            $table->text("skills")->nullable();
            $table->integer("cost")->default("1");
            $table->integer("score")->nullable();
            $table->enum("status",["open","selective","pending","failed","succeeded"])->default("open");
            $table->timestamps();
            $table->text("helper_queue")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('help_me_requests');
    }
}
