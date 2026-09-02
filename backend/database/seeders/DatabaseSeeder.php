<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ─── Users ────────────────────────────────────────────────
        DB::table('sys_users')->insert([
            ['user_code'=>'USR-0001','full_name'=>'Admin User','email'=>'admin@dost.gov.ph','password_hash'=>Hash::make('password'),'role'=>'system_admin','department'=>'IT','contact_no'=>'09171234567','is_active'=>true,'created_at'=>now(),'updated_at'=>now()],
            ['user_code'=>'USR-0002','full_name'=>'Maria Santos','email'=>'manager@dost.gov.ph','password_hash'=>Hash::make('password'),'role'=>'facility_manager','department'=>'Facilities','contact_no'=>'09189876543','is_active'=>true,'created_at'=>now(),'updated_at'=>now()],
            ['user_code'=>'USR-0003','full_name'=>'Juan dela Cruz','email'=>'maintenance@dost.gov.ph','password_hash'=>Hash::make('password'),'role'=>'maintenance_staff','department'=>'Maintenance','contact_no'=>'09201112222','is_active'=>true,'created_at'=>now(),'updated_at'=>now()],
            ['user_code'=>'USR-0004','full_name'=>'Ana Reyes','email'=>'requestor@dost.gov.ph','password_hash'=>Hash::make('password'),'role'=>'requestor','department'=>'Research','contact_no'=>'09333334444','is_active'=>true,'created_at'=>now(),'updated_at'=>now()],
            ['user_code'=>'USR-0005','full_name'=>'Pedro Bautista','email'=>'viewer@dost.gov.ph','password_hash'=>Hash::make('password'),'role'=>'viewer','department'=>'Finance','contact_no'=>'09555556666','is_active'=>true,'created_at'=>now(),'updated_at'=>now()],
        ]);

        // ─── Facilities ───────────────────────────────────────────
        DB::table('fac_facilities')->insert([
            ['facility_code'=>'FAC-001','facility_name'=>'Main Administration Building','location'=>'DOST Complex, Gen. Santos Ave, Bicutan','building'=>'Admin Bldg','floor_level'=>'All','status'=>'active','managed_by'=>2,'created_at'=>now(),'updated_at'=>now()],
            ['facility_code'=>'FAC-002','facility_name'=>'Science & Technology Laboratory','location'=>'DOST Complex, Gen. Santos Ave, Bicutan','building'=>'Lab Bldg','floor_level'=>'2F','status'=>'active','managed_by'=>2,'created_at'=>now(),'updated_at'=>now()],
            ['facility_code'=>'FAC-003','facility_name'=>'Conference Center','location'=>'DOST Complex, Gen. Santos Ave, Bicutan','building'=>'Conference Bldg','floor_level'=>'1F','status'=>'active','managed_by'=>2,'created_at'=>now(),'updated_at'=>now()],
        ]);

        // ─── Asset Categories ─────────────────────────────────────
        DB::table('ast_categories')->insert([
            ['category_code'=>'CAT-IT','category_name'=>'IT Equipment','useful_life_years'=>5,'salvage_value_pct'=>10.00,'created_at'=>now(),'updated_at'=>now()],
            ['category_code'=>'CAT-HVAC','category_name'=>'HVAC Systems','useful_life_years'=>15,'salvage_value_pct'=>5.00,'created_at'=>now(),'updated_at'=>now()],
            ['category_code'=>'CAT-ELEC','category_name'=>'Electrical Equipment','useful_life_years'=>10,'salvage_value_pct'=>5.00,'created_at'=>now(),'updated_at'=>now()],
            ['category_code'=>'CAT-FURN','category_name'=>'Furniture & Fixtures','useful_life_years'=>7,'salvage_value_pct'=>10.00,'created_at'=>now(),'updated_at'=>now()],
            ['category_code'=>'CAT-VEH','category_name'=>'Vehicles','useful_life_years'=>10,'salvage_value_pct'=>15.00,'created_at'=>now(),'updated_at'=>now()],
        ]);

        // ─── Assets ───────────────────────────────────────────────
        $assets = [
            ['asset_code'=>'AST-IT-001','asset_name'=>'Dell OptiPlex 7090 Desktop','serial_number'=>'DOPT7090-001','model'=>'OptiPlex 7090','brand'=>'Dell','category_id'=>1,'facility_id'=>1,'status'=>'active','condition'=>'good','acquisition_date'=>'2022-01-15','acquisition_cost'=>45000.00,'salvage_value'=>4500.00,'useful_life_years'=>5,'annual_depreciation'=>8100.00,'current_value'=>28800.00,'warranty_expiry_date'=>'2025-01-15','warranty_provider'=>'Dell Philippines','next_pm_date'=>'2024-07-01','pm_interval_days'=>90,'assigned_to'=>3],
            ['asset_code'=>'AST-IT-002','asset_name'=>'HP LaserJet Pro M404dn Printer','serial_number'=>'HPLJ404-002','model'=>'LaserJet Pro M404dn','brand'=>'HP','category_id'=>1,'facility_id'=>1,'status'=>'active','condition'=>'good','acquisition_date'=>'2021-06-10','acquisition_cost'=>18500.00,'salvage_value'=>1850.00,'useful_life_years'=>5,'annual_depreciation'=>3330.00,'current_value'=>8200.00,'warranty_expiry_date'=>'2024-06-10','warranty_provider'=>'HP Philippines','next_pm_date'=>'2024-06-15','pm_interval_days'=>180,'assigned_to'=>3],
            ['asset_code'=>'AST-HVAC-001','asset_name'=>'Carrier Split-Type Aircon 2.5HP','serial_number'=>'CARR25HP-001','model'=>'42KDCS25-710','brand'=>'Carrier','category_id'=>2,'facility_id'=>2,'status'=>'active','condition'=>'excellent','acquisition_date'=>'2020-03-20','acquisition_cost'=>65000.00,'salvage_value'=>3250.00,'useful_life_years'=>15,'annual_depreciation'=>4116.67,'current_value'=>41766.00,'warranty_expiry_date'=>'2025-03-20','warranty_provider'=>'Carrier Philippines','next_pm_date'=>'2024-08-01','pm_interval_days'=>90,'assigned_to'=>3],
            ['asset_code'=>'AST-ELEC-001','asset_name'=>'APC Smart-UPS 1500VA','serial_number'=>'APCUPS1500-001','model'=>'SMT1500I','brand'=>'APC','category_id'=>3,'facility_id'=>1,'status'=>'active','condition'=>'good','acquisition_date'=>'2021-09-01','acquisition_cost'=>22000.00,'salvage_value'=>1100.00,'useful_life_years'=>10,'annual_depreciation'=>2090.00,'current_value'=>15620.00,'warranty_expiry_date'=>'2024-09-01','warranty_provider'=>'APC by Schneider','next_pm_date'=>'2024-09-01','pm_interval_days'=>365,'assigned_to'=>3],
            ['asset_code'=>'AST-FURN-001','asset_name'=>'Executive Conference Table','serial_number'=>null,'model'=>'ECT-2400','brand'=>'Mandaue Foam','category_id'=>4,'facility_id'=>3,'status'=>'active','condition'=>'excellent','acquisition_date'=>'2019-11-05','acquisition_cost'=>35000.00,'salvage_value'=>3500.00,'useful_life_years'=>7,'annual_depreciation'=>4500.00,'current_value'=>17000.00,'warranty_expiry_date'=>null,'warranty_provider'=>null,'next_pm_date'=>null,'pm_interval_days'=>365,'assigned_to'=>null],
        ];

        foreach ($assets as &$a) {
            $a['created_at'] = now();
            $a['updated_at'] = now();
        }
        DB::table('ast_assets')->insert($assets);

        // ─── Work Orders ──────────────────────────────────────────
        DB::table('wko_work_orders')->insert([
            ['work_order_no'=>'WO-2024-0001','title'=>'Replace UPS Battery Cells','description'=>'Battery backup duration has degraded to under 5 minutes. Replace all battery cells.','type'=>'corrective','priority'=>'high','status'=>'completed','asset_id'=>4,'facility_id'=>1,'requested_by'=>4,'assigned_to'=>3,'approved_by'=>2,'requested_at'=>'2024-01-10 09:00:00','approved_at'=>'2024-01-10 11:00:00','started_at'=>'2024-01-11 08:00:00','completed_at'=>'2024-01-11 15:00:00','sla_hours'=>24,'sla_deadline'=>'2024-01-11 09:00:00','sla_breached'=>false,'estimated_cost'=>8500.00,'actual_cost'=>7800.00,'resolution_notes'=>'All battery cells replaced. UPS runtime restored to 45 minutes.','created_at'=>now(),'updated_at'=>now()],
            ['work_order_no'=>'WO-2024-0002','title'=>'Aircon Preventive Maintenance — Lab Bldg','description'=>'Quarterly PM for Carrier split-type aircon units in Science Laboratory.','type'=>'preventive','priority'=>'medium','status'=>'approved','asset_id'=>3,'facility_id'=>2,'requested_by'=>2,'assigned_to'=>3,'approved_by'=>2,'requested_at'=>'2024-04-01 08:00:00','approved_at'=>'2024-04-01 10:00:00','started_at'=>null,'completed_at'=>null,'sla_hours'=>72,'sla_deadline'=>'2024-04-04 08:00:00','sla_breached'=>false,'estimated_cost'=>3500.00,'actual_cost'=>null,'resolution_notes'=>null,'created_at'=>now(),'updated_at'=>now()],
            ['work_order_no'=>'WO-2024-0003','title'=>'Printer Paper Jam — Persistent Issue','description'=>'HP LaserJet printer experiencing repeated paper jams. Rollers may need replacement.','type'=>'corrective','priority'=>'medium','status'=>'in_progress','asset_id'=>2,'facility_id'=>1,'requested_by'=>4,'assigned_to'=>3,'approved_by'=>2,'requested_at'=>'2024-04-05 13:00:00','approved_at'=>'2024-04-05 14:30:00','started_at'=>'2024-04-06 09:00:00','completed_at'=>null,'sla_hours'=>48,'sla_deadline'=>'2024-04-07 13:00:00','sla_breached'=>false,'estimated_cost'=>2000.00,'actual_cost'=>null,'resolution_notes'=>null,'created_at'=>now(),'updated_at'=>now()],
            ['work_order_no'=>'WO-2024-0004','title'=>'Network Switch Failure — Emergency','description'=>'Core network switch in server room has failed. Entire floor offline.','type'=>'emergency','priority'=>'critical','status'=>'submitted','asset_id'=>null,'facility_id'=>1,'requested_by'=>4,'assigned_to'=>null,'approved_by'=>null,'requested_at'=>'2024-04-08 07:30:00','approved_at'=>null,'started_at'=>null,'completed_at'=>null,'sla_hours'=>4,'sla_deadline'=>'2024-04-08 11:30:00','sla_breached'=>true,'estimated_cost'=>null,'actual_cost'=>null,'resolution_notes'=>null,'created_at'=>now(),'updated_at'=>now()],
            ['work_order_no'=>'WO-2024-0005','title'=>'Conference Room AV Inspection','description'=>'Annual inspection of audio-visual equipment in Conference Center.','type'=>'inspection','priority'=>'low','status'=>'draft','asset_id'=>null,'facility_id'=>3,'requested_by'=>2,'assigned_to'=>null,'approved_by'=>null,'requested_at'=>'2024-04-09 10:00:00','approved_at'=>null,'started_at'=>null,'completed_at'=>null,'sla_hours'=>168,'sla_deadline'=>'2024-04-16 10:00:00','sla_breached'=>false,'estimated_cost'=>500.00,'actual_cost'=>null,'resolution_notes'=>null,'created_at'=>now(),'updated_at'=>now()],
        ]);

        // ─── Maintenance Requests ─────────────────────────────────
        DB::table('mnt_maintenance_requests')->insert([
            ['request_no'=>'MR-2024-0001','work_order_id'=>1,'asset_id'=>4,'facility_id'=>1,'submitted_by'=>4,'issue_title'=>'UPS Battery Degraded','issue_description'=>'The UPS in server room has significantly reduced backup time. Needs battery replacement.','urgency'=>'high','status'=>'converted','reviewer_notes'=>'Confirmed. Converting to work order WO-2024-0001.','reviewed_by'=>2,'reviewed_at'=>'2024-01-10 10:45:00','created_at'=>now(),'updated_at'=>now()],
            ['request_no'=>'MR-2024-0002','work_order_id'=>null,'asset_id'=>2,'facility_id'=>1,'submitted_by'=>4,'issue_title'=>'Printer Overheating','issue_description'=>'HP printer in admin office feels very hot during operation. Concerned about fire risk.','urgency'=>'medium','status'=>'pending','reviewer_notes'=>null,'reviewed_by'=>null,'reviewed_at'=>null,'created_at'=>now(),'updated_at'=>now()],
            ['request_no'=>'MR-2024-0003','work_order_id'=>null,'asset_id'=>1,'facility_id'=>1,'submitted_by'=>4,'issue_title'=>'Desktop PC Slow Performance','issue_description'=>'Computer at workstation 4 running extremely slow. May need RAM upgrade or OS reinstall.','urgency'=>'low','status'=>'reviewed','reviewer_notes'=>'Scheduled for next IT maintenance cycle.','reviewed_by'=>2,'reviewed_at'=>'2024-04-07 14:00:00','created_at'=>now(),'updated_at'=>now()],
        ]);
    }
}
