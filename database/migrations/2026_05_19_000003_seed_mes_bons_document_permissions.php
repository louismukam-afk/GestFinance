<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('route_permissions')) {
            return;
        }

        $now = now();
        $permissions = [
            [
                'route_name' => 'mes_bons.document',
                'method' => 'GET',
                'uri' => 'mes-bons/{bon}/document',
                'label' => 'Mes bons / document',
            ],
            [
                'route_name' => 'mes_bons.document_pdf',
                'method' => 'GET',
                'uri' => 'mes-bons/{bon}/document/pdf',
                'label' => 'Mes bons / document PDF',
            ],
        ];

        foreach ($permissions as $permission) {
            DB::table('route_permissions')->updateOrInsert(
                ['route_name' => $permission['route_name']],
                [
                    'method' => $permission['method'],
                    'uri' => $permission['uri'],
                    'label' => $permission['label'],
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('route_permissions')) {
            return;
        }

        DB::table('route_permissions')
            ->whereIn('route_name', ['mes_bons.document', 'mes_bons.document_pdf'])
            ->delete();
    }
};
