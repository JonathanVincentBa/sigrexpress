<?php

use App\Services\LegacySqlDumpReader;

test('reads SQL values with escaped quotes and nulls', function () {
    $path = tempnam(sys_get_temp_dir(), 'sigr-legacy-');

    file_put_contents($path, implode("\n", [
        'CREATE TABLE `persona` (`id_persona` int);',
        'INSERT INTO `otra_tabla` (`id`) VALUES (99);',
        "INSERT INTO `persona` (`id_persona`, `nombre`, `direccion`) VALUES (1, 'O''BRIEN, ANA', NULL), (2, 'SEGUNDO', 'CALLE 2');",
    ]));

    try {
        $rows = iterator_to_array((new LegacySqlDumpReader)->rows($path, 'persona'));

        expect($rows)->toBe([
            ['id_persona' => '1', 'nombre' => "O'BRIEN, ANA", 'direccion' => null],
            ['id_persona' => '2', 'nombre' => 'SEGUNDO', 'direccion' => 'CALLE 2'],
        ]);
    } finally {
        unlink($path);
    }
});

test('streams large SQL values across input chunks', function () {
    $path = tempnam(sys_get_temp_dir(), 'sigr-legacy-');
    $prefix = 'INSERT INTO `detalle` (`id_detalle`, `descripcion`, `observacion`) VALUES (1, \'';
    $description = str_repeat('A', 65534 - strlen($prefix));
    $observation = str_repeat('B', 70000);

    file_put_contents($path, $prefix.$description."', '{$observation}');");

    try {
        $rows = iterator_to_array((new LegacySqlDumpReader)->rows($path, 'detalle'));

        expect($rows)->toHaveCount(1)
            ->and($rows[0]['descripcion'])->toBe($description)
            ->and($rows[0]['observacion'])->toBe($observation);
    } finally {
        unlink($path);
    }
});

test('rejects incomplete SQL statements', function () {
    $path = tempnam(sys_get_temp_dir(), 'sigr-legacy-');

    file_put_contents($path, 'INSERT INTO `persona` (`id_persona`) VALUES (1');

    try {
        iterator_to_array((new LegacySqlDumpReader)->rows($path, 'persona'));
    } finally {
        unlink($path);
    }
})->throws(RuntimeException::class);
