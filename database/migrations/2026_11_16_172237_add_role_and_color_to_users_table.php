<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Rol: eltern / kind / gast — değerler App\Enums\Role ile aynı olmalı.
            // Varsayılan "kind": yeni biri yanlışlıkla tam yetki almasın.
            $table->string('role')->default('kind')->after('email');
            // Kişinin listelerde görünen rengi, #RRGGBB biçiminde.
            $table->string('color', 7)->default('#64748b')->after('role');
        });

        // Zaten kayıtlı ilk kullanıcı (evi kuran kişi) eltern olur,
        // yoksa kimse yeni üye ekleyemezdi.
        $firstId = DB::table('users')->min('id');
        if ($firstId !== null) {
            DB::table('users')->where('id', $firstId)->update(['role' => 'eltern']);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'color']);
        });
    }
};
