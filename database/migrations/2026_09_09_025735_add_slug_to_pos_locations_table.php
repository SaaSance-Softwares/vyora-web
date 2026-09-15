<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('pos_locations')) {
            // Table never existed on this server — create it with all columns at once
            Schema::create('pos_locations', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique()->nullable();
                $table->enum('type', ['store', 'temporary'])->default('temporary');
                $table->text('address')->nullable();
                $table->string('city')->nullable();
                $table->string('state')->nullable();
                $table->string('pincode')->nullable();
                $table->string('gst_number')->nullable();
                $table->date('start_date')->nullable();
                $table->date('end_date')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });

            // Also create pos_market_pricing if missing
            if (!Schema::hasTable('pos_market_pricing')) {
                Schema::create('pos_market_pricing', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('pos_location_id')->constrained('pos_locations')->onDelete('cascade');
                    $table->foreignId('sku_id')->constrained('skus')->onDelete('cascade');
                    $table->decimal('override_price', 10, 2)->nullable();
                    $table->integer('stock')->default(0);
                    $table->timestamps();
                });
            }
        } else {
            // Table exists — just add the slug column if not already there
            if (!Schema::hasColumn('pos_locations', 'slug')) {
                Schema::table('pos_locations', function (Blueprint $table) {
                    $table->string('slug')->unique()->nullable()->after('name');
                });
            }
            // Add city/state/pincode if missing
            if (!Schema::hasColumn('pos_locations', 'city')) {
                Schema::table('pos_locations', function (Blueprint $table) {
                    $table->string('city')->nullable()->after('address');
                    $table->string('state')->nullable()->after('city');
                    $table->string('pincode')->nullable()->after('state');
                });
            }
            // Add stock to pos_market_pricing if missing
            if (Schema::hasTable('pos_market_pricing') && !Schema::hasColumn('pos_market_pricing', 'stock')) {
                Schema::table('pos_market_pricing', function (Blueprint $table) {
                    $table->integer('stock')->default(0)->after('override_price');
                });
            }
        }

        // Backfill slugs for any existing rows that have none
        DB::table('pos_locations')
            ->whereNull('slug')
            ->orWhere('slug', '')
            ->orderBy('id')
            ->each(function ($loc) {
                $base = strtolower(preg_replace('/[^a-zA-Z0-9\s-]/', '', $loc->name));
                $slug = trim(preg_replace('/[\s-]+/', '-', $base), '-');
                $slug = $slug ?: 'store-' . $loc->id;
                // Ensure uniqueness
                $final = $slug;
                $i = 1;
                while (DB::table('pos_locations')->where('slug', $final)->where('id', '!=', $loc->id)->exists()) {
                    $final = $slug . '-' . $i++;
                }
                DB::table('pos_locations')->where('id', $loc->id)->update(['slug' => $final]);
            });
    }

    public function down(): void
    {
        if (Schema::hasColumn('pos_locations', 'slug')) {
            Schema::table('pos_locations', function (Blueprint $table) {
                $table->dropColumn('slug');
            });
        }
    }
};
