<?php

use App\Models\User;
use Database\Seeders\LegacySqlImportSeeder;
use Illuminate\Support\Facades\DB;

it('imports legacy source tables with Spatie roles and normalized city branches idempotently', function () {
    $passwordHash = bcrypt('legacy-password');
    $dumps = [];
    $writeDump = function (string $contents) use (&$dumps): string {
        $path = tempnam(sys_get_temp_dir(), 'sigr-import-');
        file_put_contents($path, $contents);
        $dumps[] = $path;

        return $path;
    };

    $files = [
        'empresa' => $writeDump(<<<'SQL'
INSERT INTO `empresa` (`id_empresa`,`razon_social`,`ruc`,`direccion`,`telefono`,`email`,`representante`,`porcentaje_impuesto`,`logo`,`estado`,`updated_at`,`created_at`) VALUES
(1,'Empresa de Prueba','1790000000001','Direccion','0990000000','EMPRESA@EXAMPLE.TEST','Representante',0,NULL,'1','2026-01-02 03:04:05','2026-01-02 03:04:05');
SQL),
        'cargo' => $writeDump(<<<'SQL'
INSERT INTO `cargo` (`id_cargo`,`descripcion`,`estado`,`updated_at`,`created_at`) VALUES
(1,'Chofer','1','0000-00-00 00:00:00','0000-00-00 00:00:00');
SQL),
        'formas_pago' => $writeDump(<<<'SQL'
INSERT INTO `formas_pago` (`id_formas_pago`,`descripcion`,`estado`,`updated_at`,`created_at`) VALUES
(1,'Efectivo','1','2026-01-02 03:04:05','2026-01-02 03:04:05');
SQL),
        'motivo_traslado' => $writeDump(<<<'SQL'
INSERT INTO `motivo_traslado` (`id_motivo_traslado`,`descripcion`,`estado`,`updated_at`,`created_at`) VALUES
(1,'Venta','1','2026-01-02 03:04:05','2026-01-02 03:04:05');
SQL),
        'rol' => $writeDump(<<<'SQL'
INSERT INTO `rol` (`id_rol`,`descripcion`,`estado`) VALUES
(1,'Administrador','1'),
(2,'Secretaria','1'),
(3,'Chofer','1'),
(4,'Cliente','1');
SQL),
        'ciudad' => $writeDump(<<<'SQL'
INSERT INTO `ciudad` (`id_ciudad`,`descripcion`,`estado`,`updated_at`,`created_at`) VALUES
(1,'Quito Sur','1','2026-01-02 03:04:05','2026-01-02 03:04:05'),
(2,'Versalles','1','2026-01-02 03:04:05','2026-01-02 03:04:05'),
(3,'Tumbaco','1','2026-01-02 03:04:05','2026-01-02 03:04:05'),
(4,'El Guambra','1','2026-01-02 03:04:05','2026-01-02 03:04:05');
SQL),
        'sucursal' => $writeDump(<<<'SQL'
INSERT INTO `sucursal` (`id_sucursal`,`id_empresa`,`id_ciudad`,`direccion`,`telefono`,`email`,`estado`,`updated_at`,`created_at`) VALUES
(1,1,1,'Direccion Agencia','0990000000','AGENCIA@EXAMPLE.TEST','1','2026-01-02 03:04:05','2026-01-02 03:04:05'),
(2,1,2,'Calle Versalles','0990000001','VERSALLES@EXAMPLE.TEST','1','2026-01-02 03:04:05','2026-01-02 03:04:05'),
(3,1,3,'Pueblo Tumbaco','0990000002','TUMBACO@EXAMPLE.TEST','1','2026-01-02 03:04:05','2026-01-02 03:04:05');
SQL),
        'persona' => $writeDump(<<<'SQL'
INSERT INTO `persona` (`id_persona`,`id_empresa`,`id_cargo`,`tipo_persona`,`nombre`,`direccion`,`tipo_dni`,`num_dni`,`telefono`,`estado`,`updated_at`,`created_at`) VALUES
(1,1,1,'E','Chofer de Prueba','Direccion','C','0100000000','0990000000','1','2026-01-02 03:04:05','2026-01-02 03:04:05');
SQL),
        'users' => $writeDump(str_replace('$passwordHash', $passwordHash, <<<'SQL'
INSERT INTO `users` (`id`,`id_rol`,`id_persona`,`name`,`email`,`password`,`remember_token`,`created_at`,`updated_at`,`estado`) VALUES
(1,1,1,'Usuario de Prueba','USER@EXAMPLE.TEST','$passwordHash','legacy-token','2026-01-02 03:04:05','2026-01-02 03:04:05','1'),
(2,2,0,'Secretaria de Prueba','SECRETARIA@EXAMPLE.TEST','$passwordHash',NULL,'2026-01-02 03:04:05','2026-01-02 03:04:05','1'),
(3,3,0,'Chofer de Prueba','CHOFER@EXAMPLE.TEST','$passwordHash',NULL,'2026-01-02 03:04:05','2026-01-02 03:04:05','1'),
(4,4,0,'Cliente de Prueba','CLIENTE@EXAMPLE.TEST','$passwordHash',NULL,'2026-01-02 03:04:05','2026-01-02 03:04:05','1');
SQL)),
        'cabecera' => $writeDump(<<<'SQL'
INSERT INTO `cabecera` (`id_cabecera`,`id_sucursal`,`id_motivo_traslado`,`id_forma_pago`,`id_users`,`id_persona`,`id_users_update`,`fecha_cancelacion`,`ciudad_origen`,`ciudad_destino`,`nom_remitente`,`dni_remitente`,`direccion_remitente`,`fono_remitente`,`nom_destinatario`,`dni_destinatario`,`direccion_destinatario`,`fono_destinatario`,`num_guia`,`num_guia_trans`,`fecha_emision`,`fecha_recepcion`,`guia_rem_cliente`,`factura_cliente`,`flete`,`recargo`,`valor_guia`,`prima`,`nombre_recibe`,`dni_recibe`,`estatus_entrega`,`estatus_cobro`,`estado`,`nota`,`updated_at`,`created_at`) VALUES
(1,1,1,1,1,1,NULL,NULL,'Quito Sur','Ibarra','Remitente','010','Direccion','099','Destinatario','020','Direccion','098',77,0,'2026-01-02 03:04:05',NULL,NULL,NULL,1,0,1,0,NULL,NULL,'0','Pendiente','1','Guia de prueba','2026-01-02 03:04:05','2026-01-02 03:04:05'),
(2,0,1,1,1,0,NULL,NULL,'Quito Sur','Ibarra','Remitente','010','Direccion','099','Destinatario','020','Direccion','098',78,0,'2026-01-02 03:04:05',NULL,NULL,NULL,1,0,1,0,NULL,NULL,'0','Pendiente','1','Guia sin sucursal ni persona asociada','2026-01-02 03:04:05','2026-01-02 03:04:05');
SQL),
        'detalle' => $writeDump(<<<'SQL'
INSERT INTO `detalle` (`id_detalle`,`id_cabecera`,`cantidad`,`descripcion`,`v_unitario`,`v_parcial`,`updated_at`,`created_at`) VALUES
(1,1,1,'Paquete de Prueba',1,1,'2026-01-02 03:04:05','2026-01-02 03:04:05'),
(2,9999,1,'Detalle sin cabecera',1,1,'2026-01-02 03:04:05','2026-01-02 03:04:05');
SQL),
    ];

    try {
        config([
            'legacy-import.files' => $files,
            'legacy-import.chunk_size' => 2,
        ]);

        DB::table('legacy_import_mappings')->insert([
            'source_table' => 'ciudad',
            'source_id' => '138',
            'target_id' => 2,
            'checksum' => hash('sha256', 'Versalles alias'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('legacy_import_mappings')->insert([
            'source_table' => 'ciudad',
            'source_id' => '139',
            'target_id' => 3,
            'checksum' => hash('sha256', 'Tumbaco alias'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        app(LegacySqlImportSeeder::class)->run();
        app(LegacySqlImportSeeder::class)->run();

        $user = User::query()->findOrFail(1);
        $secretaryUser = User::query()->findOrFail(2);
        $driverUser = User::query()->findOrFail(3);
        $clientUser = User::query()->findOrFail(4);
        $branch = DB::table('sucursal')->where('id_sucursal', 1)->first();
        $city = DB::table('ciudad')->where('id_ciudad', $branch->id_ciudad)->first();
        $versallesBranch = DB::table('sucursal')->where('id_sucursal', 2)->first();
        $tumbacoBranch = DB::table('sucursal')->where('id_sucursal', 3)->first();

        expect($user->name)->toBe('USUARIO DE PRUEBA')
            ->and($user->email)->toBe('user@example.test')
            ->and($user->hasRole('Administrador'))->toBeTrue()
            ->and($secretaryUser->hasRole('Secretaria'))->toBeTrue()
            ->and($driverUser->hasRole('Chofer'))->toBeTrue()
            ->and($clientUser->hasRole('CLIENTE'))->toBeTrue()
            ->and($user->remember_token)->toBeNull()
            ->and($branch->nombre)->toBe('Agencia Quito Sur')
            ->and($city->descripcion)->toBe('Quito')
            ->and($versallesBranch->nombre)->toBe('Agencia Versalles')
            ->and($versallesBranch->id_ciudad)->toBe($branch->id_ciudad)
            ->and($tumbacoBranch->nombre)->toBe('Agencia Tumbaco')
            ->and($tumbacoBranch->id_ciudad)->toBe($branch->id_ciudad)
            ->and(DB::table('cabecera')->where('num_guia', 77)->value('estado_tracking'))->toBe('EN BODEGA')
            ->and(DB::table('detalle')->where('id_detalle', 1)->exists())->toBeTrue();

        $this->assertDatabaseCount('users', 4);
        $this->assertDatabaseHas('cargo', ['id_cargo' => 1, 'created_at' => null, 'updated_at' => null]);
        $this->assertDatabaseCount('legacy_import_mappings', 26);
        $this->assertDatabaseHas('legacy_import_mappings', [
            'source_table' => 'ciudad',
            'source_id' => '1',
            'target_id' => $branch->id_ciudad,
        ]);
        $this->assertDatabaseHas('legacy_import_mappings', [
            'source_table' => 'ciudad',
            'source_id' => '2',
            'target_id' => $branch->id_ciudad,
        ]);
        $this->assertDatabaseHas('legacy_import_mappings', [
            'source_table' => 'ciudad',
            'source_id' => '138',
            'target_id' => $branch->id_ciudad,
        ]);
        $this->assertDatabaseHas('legacy_import_mappings', [
            'source_table' => 'ciudad',
            'source_id' => '139',
            'target_id' => $branch->id_ciudad,
        ]);
        $this->assertDatabaseHas('legacy_import_mappings', [
            'source_table' => 'ciudad',
            'source_id' => '4',
            'target_id' => $branch->id_ciudad,
        ]);
        $this->assertDatabaseMissing('ciudad', ['id_ciudad' => 1]);
        $this->assertDatabaseMissing('ciudad', ['id_ciudad' => 2]);
        $this->assertDatabaseMissing('ciudad', ['id_ciudad' => 3]);
        $this->assertDatabaseMissing('ciudad', ['id_ciudad' => 4]);
        $this->assertDatabaseMissing('sucursal', ['nombre' => 'Agencia El Guambra']);
        $this->assertDatabaseHas('cabecera', ['id_cabecera' => 2, 'id_persona' => null, 'id_sucursal' => null]);
        $this->assertDatabaseHas('detalle', ['id_detalle' => 2, 'id_cabecera' => null]);
        $this->assertDatabaseHas('roles', ['name' => 'Administrador', 'guard_name' => 'web']);

        file_put_contents($files['ciudad'], <<<'SQL'
INSERT INTO `ciudad` (`id_ciudad`,`descripcion`,`estado`,`updated_at`,`created_at`) VALUES
(1,'Guayaquil','1','2026-01-02 03:04:05','2026-01-02 03:04:05');
SQL);

        expect(fn () => app(LegacySqlImportSeeder::class)->run())
            ->toThrow(RuntimeException::class, 'El registro ciudad:1 cambió desde su importación anterior.');

        $this->assertDatabaseHas('ciudad', ['id_ciudad' => $branch->id_ciudad, 'descripcion' => 'Quito']);
    } finally {
        foreach ($dumps as $dump) {
            unlink($dump);
        }
    }
});
