<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // PSyOP Rev 1.1 — Users Table
        Schema::create('sys_users', function (Blueprint $table) {
            $table->id('user_id');
            $table->string('user_code', 20)->unique();
            $table->string('full_name', 100);
            $table->string('email', 150)->unique();
            $table->string('password_hash', 255);
            $table->enum('role', ['system_admin', 'facility_manager', 'maintenance_staff', 'requestor', 'viewer']);
            $table->string('department', 100)->nullable();
            $table->string('contact_no', 20)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // PSyOP Rev 1.1 — Facilities Table
        Schema::create('fac_facilities', function (Blueprint $table) {
            $table->id('facility_id');
            $table->string('facility_code', 20)->unique();
            $table->string('facility_name', 150);
            $table->string('location', 255);
            $table->string('building', 100)->nullable();
            $table->string('floor_level', 20)->nullable();
            $table->enum('status', ['active', 'inactive', 'under_maintenance'])->default('active');
            $table->text('description')->nullable();
            $table->foreignId('managed_by')->nullable()->constrained('sys_users', 'user_id')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        // PSyOP Rev 1.1 — Asset Categories
        Schema::create('ast_categories', function (Blueprint $table) {
            $table->id('category_id');
            $table->string('category_code', 20)->unique();
            $table->string('category_name', 100);
            $table->integer('useful_life_years')->default(5);
            $table->decimal('salvage_value_pct', 5, 2)->default(10.00);
            $table->timestamps();
        });

        // PSyOP Rev 1.1 — Assets Table (22 PSyOP columns)
        Schema::create('ast_assets', function (Blueprint $table) {
            $table->id('asset_id');
            $table->string('asset_code', 30)->unique();
            $table->string('asset_name', 150);
            $table->string('serial_number', 100)->nullable();
            $table->string('model', 100)->nullable();
            $table->string('brand', 100)->nullable();
            $table->foreignId('category_id')->constrained('ast_categories', 'category_id');
            $table->foreignId('facility_id')->constrained('fac_facilities', 'facility_id');
            $table->enum('status', ['active', 'inactive', 'under_repair', 'disposed', 'condemned'])->default('active');
            $table->enum('condition', ['excellent', 'good', 'fair', 'poor'])->default('good');
            $table->date('acquisition_date');
            $table->decimal('acquisition_cost', 15, 2);
            $table->decimal('current_value', 15, 2)->nullable();
            $table->decimal('salvage_value', 15, 2)->nullable();
            $table->integer('useful_life_years')->default(5);
            $table->decimal('annual_depreciation', 15, 2)->nullable();
            $table->date('warranty_expiry_date')->nullable();
            $table->string('warranty_provider', 150)->nullable();
            $table->string('warranty_contact', 100)->nullable();
            $table->date('last_pm_date')->nullable();
            $table->date('next_pm_date')->nullable();
            $table->integer('pm_interval_days')->default(90);
            $table->text('notes')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('sys_users', 'user_id')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        // PSyOP Rev 1.1 — Work Orders Table
        Schema::create('wko_work_orders', function (Blueprint $table) {
            $table->id('work_order_id');
            $table->string('work_order_no', 30)->unique();
            $table->string('title', 200);
            $table->text('description');
            $table->enum('type', ['corrective', 'preventive', 'emergency', 'inspection']);
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->enum('status', ['draft', 'submitted', 'approved', 'in_progress', 'completed', 'cancelled', 'rejected'])->default('draft');
            $table->foreignId('asset_id')->nullable()->constrained('ast_assets', 'asset_id')->nullOnDelete();
            $table->foreignId('facility_id')->constrained('fac_facilities', 'facility_id');
            $table->foreignId('requested_by')->constrained('sys_users', 'user_id');
            $table->foreignId('assigned_to')->nullable()->constrained('sys_users', 'user_id')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('sys_users', 'user_id')->nullOnDelete();
            $table->timestamp('requested_at')->useCurrent();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('due_date')->nullable();
            $table->integer('sla_hours')->nullable();
            $table->timestamp('sla_deadline')->nullable();
            $table->boolean('sla_breached')->default(false);
            $table->decimal('estimated_cost', 12, 2)->nullable();
            $table->decimal('actual_cost', 12, 2)->nullable();
            $table->text('resolution_notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // PSyOP Rev 1.1 — Maintenance Requests
        Schema::create('mnt_maintenance_requests', function (Blueprint $table) {
            $table->id('request_id');
            $table->string('request_no', 30)->unique();
            $table->foreignId('work_order_id')->nullable()->constrained('wko_work_orders', 'work_order_id')->nullOnDelete();
            $table->foreignId('asset_id')->nullable()->constrained('ast_assets', 'asset_id')->nullOnDelete();
            $table->foreignId('facility_id')->constrained('fac_facilities', 'facility_id');
            $table->foreignId('submitted_by')->constrained('sys_users', 'user_id');
            $table->string('issue_title', 200);
            $table->text('issue_description');
            $table->enum('urgency', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->enum('status', ['pending', 'reviewed', 'approved', 'rejected', 'converted'])->default('pending');
            $table->text('reviewer_notes')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('sys_users', 'user_id')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });

        // PSyOP Rev 1.1 — Audit Logs
        Schema::create('aud_audit_logs', function (Blueprint $table) {
            $table->id('log_id');
            $table->foreignId('user_id')->nullable()->constrained('sys_users', 'user_id')->nullOnDelete();
            $table->string('action', 50);
            $table->string('entity_type', 50);
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aud_audit_logs');
        Schema::dropIfExists('mnt_maintenance_requests');
        Schema::dropIfExists('wko_work_orders');
        Schema::dropIfExists('ast_assets');
        Schema::dropIfExists('ast_categories');
        Schema::dropIfExists('fac_facilities');
        Schema::dropIfExists('sys_users');
    }
};
