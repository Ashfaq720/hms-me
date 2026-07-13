<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ipd_nurse_note_replies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ipd_nurse_note_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_name')->nullable();
            $table->string('user_role')->nullable();
            $table->text('reply');
            $table->timestamps();

            $table->foreign('ipd_nurse_note_id', 'innr_note_fk')
                ->references('id')->on('ipd_nurse_notes')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ipd_nurse_note_replies');
    }
};
