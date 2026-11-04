<?php

return [
    'files' => [
        'empresa' => env('LEGACY_EMPRESA_SQL'),
        'cargo' => env('LEGACY_CARGO_SQL'),
        'formas_pago' => env('LEGACY_FORMAS_PAGO_SQL'),
        'motivo_traslado' => env('LEGACY_MOTIVO_TRASLADO_SQL'),
        'rol' => env('LEGACY_ROL_SQL'),
        'ciudad' => env('LEGACY_CIUDAD_SQL'),
        'sucursal' => env('LEGACY_SUCURSAL_SQL'),
        'persona' => env('LEGACY_PERSONA_SQL'),
        'users' => env('LEGACY_USERS_SQL'),
        'cabecera' => env('LEGACY_CABECERA_SQL'),
        'detalle' => env('LEGACY_DETALLE_SQL'),
    ],
    'chunk_size' => 250,
];
