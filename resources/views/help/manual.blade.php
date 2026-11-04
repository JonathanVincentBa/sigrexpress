<x-layouts.app :title="'Manual de Ayuda'">
<div>
    <div class="page-heading">
        <div><p class="eyebrow">Soporte y documentación</p><h1>Manual de Ayuda</h1><p class="muted">Guía de uso del sistema SIGRExpress.</p></div>
    </div>
    @if ($manualExists)
        <section class="panel" style="padding:18px;">
            <div class="panel-toolbar" style="padding:0 0 14px;">
                <strong>Manual de usuario</strong>
                <a class="button primary" href="{{ route('manual.download') }}">Descargar PDF</a>
            </div>
            <iframe src="{{ $manualUrl }}" title="Manual de usuario de SIGRExpress" style="width:100%;height:75vh;border:1px solid #e8edf4;border-radius:8px;"></iframe>
        </section>
    @else
        <section class="panel" style="padding:24px;">
            <h2>El manual aún no está publicado</h2>
            <p class="muted" style="margin-top:8px;">Coloca el archivo PDF aprobado en <code>storage/app/public/manual/manual_usuario.pdf</code>. Después de publicarlo, se habilitarán su previsualización y descarga.</p>
        </section>
    @endif
</div>
</x-layouts.app>
