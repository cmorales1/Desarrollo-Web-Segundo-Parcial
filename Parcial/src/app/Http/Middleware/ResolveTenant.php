<?php
namespace App\Http\Middleware;
use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class ResolveTenant
{
    public function handle(Request $request, Closure $next)
    {
        $host = $request->getHost(); // empresa1.local
        $parts = explode('.', $host);
        $sub = $parts[0] ?? null;

        if (!$sub || in_array($sub, ['www'])) {
            return response()->json(['message'=>'Subdominio inválido'], 400);
        }

        $tenant = Tenant::where('subdomain', $sub)->first();
        if (!$tenant) { return response()->json(['message'=>'Tenant no encontrado'], 404); }

        // Ajustar conexión pgsql base (misma DB) y setear search_path al schema del tenant
        $base = Config::get('database.connections.pgsql');
        // Opcionalmente podrías cambiar de DB si cada tenant usa DB distinta:
        // $base['database'] = $tenant->database;
        Config::set('database.connections.tenant', $base);
        DB::purge('tenant');
        DB::reconnect('tenant');

        // Cambiar el search_path a su schema (creado por seeder/comando)
        DB::connection('tenant')->statement('set search_path to "'.$tenant->schema.'"');
        app()->instance('currentTenant', $tenant);

        return $next($request);
    }
}