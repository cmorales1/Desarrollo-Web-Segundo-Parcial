<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('subdomain')->unique();
            $table->string('schema'); // nombre del schema en PG (empresa1, empresa2, etc.)
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('tenants'); }
};