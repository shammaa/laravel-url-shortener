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
        Schema::create('short_links', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique()->index();
            $table->text('destination_url');
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            
            // Password protection
            $table->string('password')->nullable();
            $table->boolean('password_protected')->default(false);
            
            // Expiry settings
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            
            // Click limits
            $table->unsignedBigInteger('click_limit')->nullable();
            $table->unsignedBigInteger('clicks_count')->default(0);
            
            // Tracking settings
            $table->boolean('track_visits')->default(true);
            $table->boolean('track_ip_address')->default(true);
            $table->boolean('track_user_agent')->default(true);
            $table->boolean('track_referer')->default(true);
            $table->boolean('track_geo')->default(false);
            
            // UTM tracking (stored as JSON for flexibility)
            $table->json('utm_parameters')->nullable();
            $table->boolean('utm_hidden')->default(true);
            
            // Custom settings
            $table->string('redirect_status_code')->default('302'); // 301, 302
            $table->string('custom_domain')->nullable();
            
            // QR Code
            $table->text('qr_code_path')->nullable();
            
            // User relationship (optional - if you want to track link owners)
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('user_type')->nullable(); // For polymorphic relationship
            
            // Polymorphic relationship (for linking to any model)
            $table->morphs('short_linkable'); // Creates short_linkable_type and short_linkable_id
            
            // Metadata
            $table->json('metadata')->nullable(); // For custom data
            $table->json('tags')->nullable(); // For link categorization
            $table->string('group')->nullable()->index(); // For grouping links
            
            // Statistics
            $table->timestamp('first_clicked_at')->nullable();
            $table->timestamp('last_clicked_at')->nullable();
            
            // Timestamps
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for performance
            $table->index('is_active');
            $table->index('expires_at');
            $table->index(['user_id', 'user_type']);
            $table->index('group');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('short_links');
    }
};
