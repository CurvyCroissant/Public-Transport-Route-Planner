<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('transit_routes', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->float('on_time_rate')->nullable();
            $table->timestamps();
        });

        Schema::create('stops', function (Blueprint $table) {
            $table->id();
            $table->string('route_id');
            $table->string('stop_key')->unique();
            $table->string('name');
            $table->decimal('lat', 10, 6)->nullable();
            $table->decimal('lng', 10, 6)->nullable();
            $table->timestamps();

            $table->foreign('route_id')->references('id')->on('transit_routes')->cascadeOnDelete();
            $table->index(['route_id']);
        });

        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('route_id');
            $table->string('vehicle_key')->unique();
            $table->string('label');
            $table->decimal('lat', 10, 6)->nullable();
            $table->decimal('lng', 10, 6)->nullable();
            $table->boolean('live')->default(false);
            $table->timestamps();

            $table->foreign('route_id')->references('id')->on('transit_routes')->cascadeOnDelete();
            $table->index(['route_id', 'live']);
        });

        Schema::create('notices', function (Blueprint $table) {
            $table->id();
            $table->string('route_id');
            $table->string('type')->nullable();
            $table->string('severity')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();

            $table->foreign('route_id')->references('id')->on('transit_routes')->cascadeOnDelete();
            $table->index(['route_id']);
        });

        Schema::create('arrivals', function (Blueprint $table) {
            $table->id();
            $table->string('route_id');
            $table->string('stop_key');
            $table->string('vehicle_key');
            $table->unsignedInteger('minutes');
            $table->boolean('live')->default(false);
            $table->timestamps();

            $table->foreign('route_id')->references('id')->on('transit_routes')->cascadeOnDelete();
            $table->index(['route_id', 'stop_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('arrivals');
        Schema::dropIfExists('notices');
        Schema::dropIfExists('vehicles');
        Schema::dropIfExists('stops');
        Schema::dropIfExists('transit_routes');
    }
};
