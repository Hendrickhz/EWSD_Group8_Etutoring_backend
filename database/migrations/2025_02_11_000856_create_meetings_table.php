<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('meetings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tutor_id')->constrained('users','id')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users','id')->cascadeOnDelete();
            $table->string('title');
            $table->text('notes')->nullable();
            $table->enum('type',['virtual','in-person']);
            $table->string('location')->nullable();
            $table->string('platform')->nullable();
            $table->string('meeting_link')->nullable();
            $table->date('date')->nullable();
            $table->time('time')->nullable();
            $table->enum('status',['pending','confirmed','cancelled'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meetings');
    }
};
