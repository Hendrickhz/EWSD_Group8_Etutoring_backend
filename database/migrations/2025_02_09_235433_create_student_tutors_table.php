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
        Schema::create('student_tutors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users','id')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('tutor_id')->constrained('users','id')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_tutors');
    }
};
