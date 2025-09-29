<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Tenant;

class TenantSeeder extends Seeder {
    public function run(): void {
        Tenant::updateOrCreate(['subdomain'=>'empresa1'], ['name'=>'Empresa 1','schema'=>'empresa1']);
        Tenant::updateOrCreate(['subdomain'=>'empresa2'], ['name'=>'Empresa 2','schema'=>'empresa2']);
    }
}