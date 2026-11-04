<?php

namespace App\Livewire\Tracking;

use App\Models\Cabecera;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.guest')]
#[Title('Seguimiento de Guía')]
class TrackingGuia extends Component
{
    public string $num_guia = '';

    public string $identificacion = '';

    #[Locked]
    public ?int $matchedGuideId = null;

    public function updatedNumGuia(): void
    {
        $this->matchedGuideId = null;
        $this->resetValidation();
    }

    public function updatedIdentificacion(): void
    {
        $this->matchedGuideId = null;
        $this->resetValidation();
    }

    public function search(): void
    {
        $this->matchedGuideId = null;
        $this->num_guia = trim($this->num_guia);
        $this->identificacion = mb_strtoupper(trim($this->identificacion));

        $validated = $this->validate([
            'num_guia' => ['required', 'integer', 'min:1'],
            'identificacion' => ['required', 'string', 'max:20'],
        ], [
            'num_guia.required' => 'Ingresa el número de guía.',
            'num_guia.integer' => 'El número de guía debe ser válido.',
            'identificacion.required' => 'Ingresa la identificación del destinatario.',
            'identificacion.max' => 'La identificación no puede superar los 20 caracteres.',
        ]);

        $key = 'guide-tracking:'.(request()->ip() ?? 'unknown');

        if (RateLimiter::tooManyAttempts($key, 10)) {
            $this->addError('lookup', 'Se alcanzó el límite de consultas. Inténtalo nuevamente en un minuto.');

            return;
        }

        RateLimiter::hit($key, 60);

        $guideId = Cabecera::query()
            ->where('estado', '1')
            ->where('num_guia', (int) $validated['num_guia'])
            ->where('dni_destinatario', trim($validated['identificacion']))
            ->value('id_cabecera');

        if ($guideId === null) {
            $this->addError('lookup', 'No encontramos una guía con esos datos. Verifica el número y la identificación del destinatario.');

            return;
        }

        $this->matchedGuideId = (int) $guideId;
    }

    public function render()
    {
        $guide = $this->matchedGuideId === null
            ? null
            : Cabecera::query()
                ->with('detalles:id_detalle,id_cabecera,descripcion,cantidad')
                ->where('estado', '1')
                ->find($this->matchedGuideId);

        return view('livewire.tracking.tracking-guia', [
            'guide' => $guide,
        ]);
    }
}
