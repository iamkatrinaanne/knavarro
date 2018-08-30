<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class QuestionBank extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('questionbank', function (Blueprint $table) {
           // $table->increments('id');
            // $table->timestamps();
            $table->uuid('question_ID',20)->primary();
            $table->uuid('lesson_ID');
            $table->String('question',255);
            $table->String('correctanswer');
            $table->integer('difficulty');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('questionbank');
    }
}
