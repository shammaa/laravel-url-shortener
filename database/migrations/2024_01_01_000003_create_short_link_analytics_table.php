<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This table stores aggregated analytics data for faster reporting.
     * Data is aggregated daily for better performance.
     */
    public function up(): void
    {
        Schema::create('short_link_analytics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('short_link_id')->constrained()->onDelete('cascade');
            
            // Date aggregation
            $table->date('date')->index();
            
            // Click counts
            $table->unsignedBigInteger('total_clicks')->default(0);
            $table->unsignedBigInteger('unique_clicks')->default(0);
            $table->unsignedBigInteger('unique_visitors')->default(0);
            
            // Geographic breakdown
            $table->json('clicks_by_country')->nullable(); // {country_code: count}
            $table->json('clicks_by_city')->nullable(); // {city: count}
            
            // Device breakdown
            $table->json('clicks_by_device')->nullable(); // {device_type: count}
            $table->json('clicks_by_platform')->nullable(); // {platform: count}
            $table->json('clicks_by_browser')->nullable(); // {browser: count}
            
            // Referrer breakdown
            $table->json('clicks_by_referer')->nullable(); // {domain: count}
            
            // UTM breakdown (hidden tracking)
            $table->json('clicks_by_utm_source')->nullable();
            $table->json('clicks_by_utm_medium')->nullable();
            $table->json('clicks_by_utm_campaign')->nullable();
            
            // Hourly breakdown (24 hours)
            $table->json('clicks_by_hour')->nullable(); // {hour: count}
            
            // Timestamps
            $table->timestamps();
            
            // Unique index to prevent duplicate aggregations
            $table->unique(['short_link_id', 'date']);
            $table->index('date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('short_link_analytics');
    }
};
