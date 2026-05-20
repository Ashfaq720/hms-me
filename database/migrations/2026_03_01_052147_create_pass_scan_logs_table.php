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
        Schema::create('pass_scan_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('pass_id')->constrained('passes')->cascadeOnDelete();
            $t->string('scan_type', 10); // ENTRY/EXIT
            $t->string('result', 20);    // ALLOWED/DENIED
            $t->text('reason')->nullable();
            $t->foreignId('scanned_by')->nullable()->constrained('users')->nullOnDelete(); // security user
            $t->dateTime('scanned_at');
            $t->timestamps();

            $t->index(['pass_id', 'scanned_at']);
            $t->index(['result', 'scanned_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pass_scan_logs');
    }
};
