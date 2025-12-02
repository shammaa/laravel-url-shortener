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
        Schema::create('short_link_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('short_link_id')->constrained()->onDelete('cascade');
            
            // IP and Location
            $table->string('ip_address', 45)->nullable()->index();
            $table->string('country')->nullable()->index();
            $table->string('country_code', 2)->nullable()->index();
            $table->string('city')->nullable()->index();
            $table->string('region')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            
            // User Agent Info
            $table->text('user_agent')->nullable();
            $table->string('device_type')->nullable()->index(); // desktop, mobile, tablet
            $table->string('device_name')->nullable();
            $table->string('platform')->nullable()->index(); // Windows, macOS, Linux, iOS, Android
            $table->string('platform_version')->nullable();
            $table->string('browser')->nullable()->index(); // Chrome, Firefox, Safari, Edge
            $table->string('browser_version')->nullable();
            $table->boolean('is_bot')->default(false)->index();
            $table->boolean('is_mobile')->default(false)->index();
            $table->boolean('is_tablet')->default(false)->index();
            
            // Referrer
            $table->text('referer_url')->nullable();
            $table->string('referer_domain')->nullable()->index();
            
            // UTM Parameters (hidden tracking)
            $table->json('utm_source')->nullable();
            $table->json('utm_medium')->nullable();
            $table->json('utm_campaign')->nullable();
            $table->json('utm_term')->nullable();
            $table->json('utm_content')->nullable();
            
            // Additional data
            $table->json('query_parameters')->nullable();
            $table->string('language', 10)->nullable();
            $table->string('timezone')->nullable();
            
            // Session tracking
            $table->string('session_id')->nullable()->index();
            
            // Timestamps
            $table->timestamp('visited_at')->useCurrent()->index();
            $table->timestamps();
            
            // Indexes for analytics queries
            $table->index(['short_link_id', 'visited_at']);
            $table->index(['country_code', 'visited_at']);
            $table->index(['browser', 'visited_at']);
            $table->index(['platform', 'visited_at']);
            $table->index(['device_type', 'visited_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('short_link_visits');
    }
};
