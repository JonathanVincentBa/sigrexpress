const normalizeSearchText = (value) => value
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .toLocaleLowerCase('es');

let searchableSelectId = 0;

const initializeSearchableSelect = (wrapper) => {
    const searchInput = wrapper.querySelector('[data-searchable-select-input]');
    const select = wrapper.querySelector('[data-searchable-select-control]');
    const trigger = wrapper.querySelector('[data-searchable-select-trigger]');
    const selectedValue = wrapper.querySelector('[data-searchable-select-value]');
    const menu = wrapper.querySelector('[data-searchable-select-menu]');
    const optionsList = wrapper.querySelector('[data-searchable-select-options]');
    const emptyMessage = wrapper.querySelector('.searchable-select-empty');

    if (!searchInput || !select || !trigger || !selectedValue || !menu || !optionsList || !emptyMessage) {
        return;
    }

    if (!optionsList.id) {
        optionsList.id = `${select.id || 'searchable-select'}-options-${++searchableSelectId}`;
        trigger.setAttribute('aria-controls', optionsList.id);
    }

    const refreshOptions = () => {
        const selectedOption = select.selectedOptions[0];
        selectedValue.textContent = selectedOption?.textContent?.trim() || 'Selecciona una opción';
        optionsList.replaceChildren();

        const query = normalizeSearchText(searchInput.value.trim());

        Array.from(select.options).forEach((option) => {
            if (query && !normalizeSearchText(option.textContent ?? '').includes(query)) {
                return;
            }

            const optionButton = document.createElement('button');

            optionButton.type = 'button';
            optionButton.className = 'searchable-select-option';
            optionButton.setAttribute('role', 'option');
            optionButton.setAttribute('aria-selected', option.selected ? 'true' : 'false');
            optionButton.dataset.value = option.value;
            optionButton.textContent = option.textContent?.trim() ?? '';
            optionButton.disabled = option.disabled;

            optionsList.append(optionButton);
        });

        emptyMessage.hidden = optionsList.childElementCount > 0;
        emptyMessage.textContent = `No se encontraron opciones para "${searchInput.value.trim()}".`;
    };

    const openMenu = (currentWrapper) => {
        const currentMenu = currentWrapper.querySelector('[data-searchable-select-menu]');
        const currentTrigger = currentWrapper.querySelector('[data-searchable-select-trigger]');
        const currentSearch = currentWrapper.querySelector('[data-searchable-select-input]');

        currentWrapper.dataset.open = 'true';
        currentMenu.hidden = false;
        currentTrigger.setAttribute('aria-expanded', 'true');
        currentSearch.value = '';
        initializeSearchableSelect(currentWrapper);
        currentSearch.focus();
    };

    const closeMenu = (currentWrapper, restoreFocus = false) => {
        const currentMenu = currentWrapper.querySelector('[data-searchable-select-menu]');
        const currentTrigger = currentWrapper.querySelector('[data-searchable-select-trigger]');
        const currentSearch = currentWrapper.querySelector('[data-searchable-select-input]');

        delete currentWrapper.dataset.open;
        currentMenu.hidden = true;
        currentTrigger.setAttribute('aria-expanded', 'false');
        currentSearch.value = '';
        initializeSearchableSelect(currentWrapper);

        if (restoreFocus) {
            currentTrigger.focus();
        }
    };

    const focusOption = (currentWrapper, direction) => {
        const optionButtons = Array.from(currentWrapper.querySelectorAll('.searchable-select-option:not(:disabled)'));
        const target = direction === 'first' ? optionButtons[0] : optionButtons.at(-1);
        target?.focus();
    };

    wrapper.classList.add('is-enhanced');
    trigger.hidden = false;
    trigger.disabled = select.disabled;
    searchInput.disabled = select.disabled;

    if (wrapper.dataset.searchableSelectInitialized !== 'true') {
        wrapper.dataset.searchableSelectInitialized = 'true';
        wrapper.addEventListener('click', (event) => {
            if (!(event.target instanceof Element)) {
                return;
            }

            const optionButton = event.target.closest('.searchable-select-option');

            if (optionButton && wrapper.contains(optionButton)) {
                const currentSelect = wrapper.querySelector('[data-searchable-select-control]');
                currentSelect.value = optionButton.dataset.value;
                currentSelect.dispatchEvent(new Event('input', { bubbles: true }));
                currentSelect.dispatchEvent(new Event('change', { bubbles: true }));
                closeMenu(wrapper, true);
                return;
            }

            if (event.target.closest('[data-searchable-select-trigger]')) {
                if (wrapper.dataset.open === 'true') {
                    closeMenu(wrapper);
                } else {
                    openMenu(wrapper);
                }
            }
        });
        wrapper.addEventListener('input', (event) => {
            if (event.target.matches('[data-searchable-select-input]')) {
                initializeSearchableSelect(wrapper);
            }
        });
        wrapper.addEventListener('change', (event) => {
            if (event.target.matches('[data-searchable-select-control]')) {
                initializeSearchableSelect(wrapper);
            }
        });
        wrapper.addEventListener('keydown', (event) => {
            if (event.target.matches('[data-searchable-select-input]')) {
                if (event.key === 'ArrowDown') {
                    event.preventDefault();
                    focusOption(wrapper, 'first');
                } else if (event.key === 'ArrowUp') {
                    event.preventDefault();
                    focusOption(wrapper, 'last');
                } else if (event.key === 'Escape') {
                    event.preventDefault();
                    closeMenu(wrapper, true);
                } else if (event.key === 'Enter') {
                    event.preventDefault();
                    wrapper.querySelector('.searchable-select-option:not(:disabled)')?.click();
                }

                return;
            }

            const currentOption = event.target.closest('.searchable-select-option');

            if (!currentOption) {
                return;
            }

            if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
                event.preventDefault();
                const optionButtons = Array.from(wrapper.querySelectorAll('.searchable-select-option:not(:disabled)'));
                const currentIndex = optionButtons.indexOf(currentOption);
                const nextIndex = currentIndex + (event.key === 'ArrowDown' ? 1 : -1);

                optionButtons[(nextIndex + optionButtons.length) % optionButtons.length]?.focus();
            } else if (event.key === 'Escape') {
                event.preventDefault();
                closeMenu(wrapper, true);
            }
        });
    }

    refreshOptions();
};

const initializeSearchableSelects = (root = document) => {
    if (root instanceof Element && root.matches('[data-searchable-select]')) {
        initializeSearchableSelect(root);
    }

    root.querySelectorAll?.('[data-searchable-select]').forEach(initializeSearchableSelect);
};

document.addEventListener('click', (event) => {
    if (!(event.target instanceof Element)) {
        return;
    }

    document.querySelectorAll('[data-searchable-select][data-open="true"]').forEach((wrapper) => {
        if (!wrapper.contains(event.target)) {
            const menu = wrapper.querySelector('[data-searchable-select-menu]');
            const trigger = wrapper.querySelector('[data-searchable-select-trigger]');
            menu.hidden = true;
            trigger.setAttribute('aria-expanded', 'false');
            delete wrapper.dataset.open;
        }
    });
});

document.addEventListener('DOMContentLoaded', () => initializeSearchableSelects());

const registerLivewireHooks = () => {
    if (!window.Livewire) {
        return;
    }

    window.Livewire.hook('morph.added', ({ el }) => initializeSearchableSelects(el));
    window.Livewire.hook('morph.updated', ({ el }) => {
        const wrapper = el instanceof Element ? el.closest('[data-searchable-select]') : null;

        if (wrapper) {
            initializeSearchableSelect(wrapper);
        }
    });
};

if (window.Livewire) {
    registerLivewireHooks();
} else {
    document.addEventListener('livewire:init', registerLivewireHooks, { once: true });
}