<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_makes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->char('country_code', 2)->nullable();
            $table->timestamps();
        });

        Schema::create('vehicle_models', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_make_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('model_code')->nullable();
            $table->unsignedSmallInteger('production_start_year')->nullable();
            $table->unsignedSmallInteger('production_end_year')->nullable();
            $table->timestamps();

            $table->unique(['vehicle_make_id', 'slug']);
        });

        Schema::create('vehicle_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_model_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('drive_system', 30);
            $table->string('fuel_type', 30);
            $table->decimal('fuel_efficiency', 5, 2);
            $table->string('fuel_efficiency_unit', 10)->default('km/L');
            $table->string('fuel_efficiency_standard', 30)->nullable();
            $table->unsignedSmallInteger('model_year')->nullable();
            $table->string('source_name')->nullable();
            $table->text('source_url')->nullable();
            $table->string('source_record_id')->nullable();
            $table->date('source_updated_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['source_name', 'source_record_id']);
            $table->index(['vehicle_model_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_variants');
        Schema::dropIfExists('vehicle_models');
        Schema::dropIfExists('vehicle_makes');
    }
};
