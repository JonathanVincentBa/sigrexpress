<div class="searchable-select" data-searchable-select>
    <div wire:ignore>
        <button
            id="{{ $attributes->get('id') }}"
            type="button"
            class="control searchable-select-trigger"
            @if ($attributes->get('aria-label')) aria-label="{{ $attributes->get('aria-label') }}" @endif
            aria-haspopup="listbox"
            aria-expanded="false"
            data-searchable-select-trigger
            hidden
            @disabled($attributes->has('disabled'))
        >
            <span data-searchable-select-value></span>
            <span class="searchable-select-chevron" aria-hidden="true">⌄</span>
        </button>

        <div class="searchable-select-menu" data-searchable-select-menu hidden>
            <input
                type="search"
                class="control searchable-select-search"
                placeholder="Escribe para buscar..."
                aria-label="Buscar opciones"
                autocomplete="off"
                data-searchable-select-input
                @disabled($attributes->has('disabled'))
            >
            <div class="searchable-select-options" role="listbox" data-searchable-select-options></div>
            <p class="searchable-select-empty" role="status" aria-live="polite" hidden>
                No se encontraron opciones.
            </p>
        </div>
    </div>

    <select {{ $attributes->except(['id']) }} data-searchable-select-control aria-hidden="true" tabindex="-1">
        {{ $slot }}
    </select>
</div>
