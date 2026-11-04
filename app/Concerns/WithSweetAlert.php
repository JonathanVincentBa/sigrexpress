<?php

namespace App\Concerns;

use RealRashid\SweetAlert\Facades\Alert;

trait WithSweetAlert
{
    /**
     * Dispara una alerta de éxito.
     */
    public function alertSuccess(): void
    {
        if (method_exists($this, 'dispatch')) {
            $this->dispatch('swal', icon: 'success');

            return;
        }

        Alert::success('Su registro ha sido guardado exitosamente');
    }

    /**
     * Dispara una alerta de error.
     */
    public function alertError(string $title, string $message = ''): void
    {
        Alert::error($title, $message);

        if (method_exists($this, 'dispatch')) {
            $this->dispatch('swal', icon: 'error', title: $title, text: $message);
        }
    }

    /**
     * Dispara una alerta de advertencia.
     */
    public function alertWarning(string $title, string $message = ''): void
    {
        Alert::warning($title, $message);

        if (method_exists($this, 'dispatch')) {
            $this->dispatch('swal', icon: 'warning', title: $title, text: $message);
        }
    }

    /**
     * Dispara una alerta informativa.
     */
    public function alertInfo(string $title, string $message = ''): void
    {
        Alert::info($title, $message);

        if (method_exists($this, 'dispatch')) {
            $this->dispatch('swal', icon: 'info', title: $title, text: $message);
        }
    }

    /**
     * Dispara una notificación tipo Toast.
     */
    public function alertToast(string $message, string $type = 'success', string $position = 'top-end'): void
    {
        if ($type === 'success') {
            $this->alertSuccess();

            return;
        }

        Alert::toast($message, $type);

        if (method_exists($this, 'dispatch')) {
            $this->dispatch('swal:toast', type: $type, message: $message, position: $position);
        }
    }
}
