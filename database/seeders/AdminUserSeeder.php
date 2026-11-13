<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call(LegacyAccessControlSeeder::class);

        DB::transaction(function (): void {
            $adminEmail = Str::lower(trim((string) config('app.admin_email')));
            $adminPassword = (string) config('app.admin_password');
            $adminName = Str::upper(trim((string) config('app.admin_name', 'Jonathan Daniel Vincent Basantes')));

            if ($adminEmail === '' && $adminPassword === '') {
                Log::info('Se omitió crear el administrador inicial; configure ADMIN_EMAIL y ADMIN_PASSWORD para habilitarlo.');

                return;
            }

            if ($adminEmail === '' || $adminPassword === '') {
                throw new RuntimeException('Configure ADMIN_EMAIL y ADMIN_PASSWORD antes de crear el usuario administrador inicial.');
            }

            $adminUser = User::query()->where('email', $adminEmail)->first();

            if ($adminUser === null && User::query()->where('name', $adminName)->exists()) {
                throw new RuntimeException('Ya existe un usuario con el nombre del administrador inicial y otro correo. Configure ADMIN_EMAIL con su correo actual.');
            }

            $adminUser ??= User::query()->create([
                'name' => $adminName,
                'email' => $adminEmail,
                'password' => $adminPassword,
                'estado' => '1',
            ]);

            if ($adminUser->email_verified_at === null) {
                $adminUser->forceFill(['email_verified_at' => now()])->save();
            }

            $adminUser->assignRole('Super Admin');
        });
    }
}
