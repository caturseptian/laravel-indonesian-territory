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
        Schema::create('provinces', function (Blueprint $table) {
            $table->string('code', 2)->primary();
            $table->string('name');
        });

        Schema::create('regencies', function (Blueprint $table) {
            $table->string('code', 5)->primary();
            $table->string('province_code', 2);
            $table->string('type', 9);
            $table->string('name');

            $table->foreign('province_code')->references('code')->on('provinces')->cascadeOnDelete();
            $table->index('name');
        });

        Schema::create('districts', function (Blueprint $table) {
            $table->string('code', 8)->primary();
            $table->string('regency_code', 5);
            $table->string('name');

            $table->foreign('regency_code')->references('code')->on('regencies')->cascadeOnDelete();
            $table->index('name');
        });

        Schema::create('villages', function (Blueprint $table) {
            $table->string('code', 13)->primary();
            $table->string('district_code', 8);
            $table->string('type', 9);
            $table->string('name');
            $table->string('postal_code', 5)->nullable();

            $table->foreign('district_code')->references('code')->on('districts')->cascadeOnDelete();
            $table->index('name');
            $table->index('postal_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('villages');
        Schema::dropIfExists('districts');
        Schema::dropIfExists('regencies');
        Schema::dropIfExists('provinces');
    }
};
