<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLessonsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('chapter_id')->unsigned();
            $table->string('title');
            $table->text('description')->nullable();
            $table->time('duration')->nullable();
            $table->string('video')->nullable();
            $table->string('audio')->nullable();
            $table->string('doc')->nullable();
            $table->integer('position');
            $table->boolean('is_draft')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('chapter_id')
                  ->references('id')
                  ->on('chapters')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lessons');
    }
}
