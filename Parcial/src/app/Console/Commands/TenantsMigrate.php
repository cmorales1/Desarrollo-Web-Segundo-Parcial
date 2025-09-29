<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;
use App\Models\Tenant;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class TenantsMigrate extends Command
{
    protected $signature = 'tenants:migrate';
    protected $description = 'Crea schema si no existe y ejecuta migraciones para cada tenant (PostgreSQL).';

    public function handle()
    {
        foreach (Tenant::all() as $tenant) {
            $this->info("Migrando tenant: {$tenant->subdomain} (schema={$tenant->schema})");

            $base = Config::get('database.connections.pgsql');
            Config::set('database.connections.tenant', $base);
            DB::purge('tenant');
            DB::reconnect('tenant');

            // Crear schema si no existe y cambiar search_path
            DB::connection('tenant')->statement('create schema if not exists "'.$tenant->schema.'";');
            DB::connection('tenant')->statement('set search_path to "'.$tenant->schema.'"');

            // Ejecutar migraciones de carpeta tenant (usará el search_path actual)
            $this->call('migrate', [
                '--database' => 'tenant',
                '--path' => 'database/migrations_tenant',
                '--force' => true,
            ]);
        }
        $this->info('Migraciones de tenants completadas.');
        return self::SUCCESS;
    }
}