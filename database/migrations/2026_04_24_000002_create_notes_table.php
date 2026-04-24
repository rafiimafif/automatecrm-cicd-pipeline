<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('notes', function (Blueprint $table) {
            $table->id();
            $table->morphs('notable');
            $table->enum('type', ['call', 'email', 'meeting', 'note'])->default('note');
            $table->text('content');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index(['notable_type', 'notable_id']);
            $table->index('type');
        });
    }

    public function down()
    {
        Schema::dropIfExists('notes');
    }
};
