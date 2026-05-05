<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('security_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('question');
            $table->string('answer'); // Hashed with Hash::make()
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->boolean('security_questions_setup')->default(false)->after('remember_token');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('security_questions');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('security_questions_setup');
        });
    }
};
