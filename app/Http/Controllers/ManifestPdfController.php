<?php

namespace App\Http\Controllers;

use App\Models\Manifiesto;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class ManifestPdfController extends Controller
{
    public function __invoke(Manifiesto $manifest): Response
    {
        $manifest->load([
            'chofer',
            'vehiculo',
            'usuario',
            'guias.detalles',
            'guias.sucursal.ciudad',
        ]);

        return Pdf::loadView('manifiestos.pdf', [
            'manifest' => $manifest,
        ])->setPaper('a4', 'portrait')->download($manifest->codigo.'.pdf');
    }
}
