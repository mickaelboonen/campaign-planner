import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = [
        'cell',
        'statusButton',
        'input',
        'saveButton',
    ];

    static values = {
        activeStatus: {
            type: String,
            default: 'available',
        },
    };

    connect() {
        this.hasUnsavedChanges = false;

        this.beforeUnloadHandler = (event) => {
            if (!this.hasUnsavedChanges) {
                return;
            }

            event.preventDefault();
            event.returnValue = '';
        };

        this.beforeVisitHandler = (event) => {
            if (!this.hasUnsavedChanges) {
                return;
            }

            const shouldLeave = window.confirm(
                'Vous avez des modifications non enregistrées. Voulez-vous vraiment quitter cette page ?',
            );

            if (!shouldLeave) {
                event.preventDefault();
            }
        };

        window.addEventListener(
            'beforeunload',
            this.beforeUnloadHandler,
        );

        document.addEventListener(
            'turbo:before-visit',
            this.beforeVisitHandler,
        );

        this.updateStatusButtons();
        this.updateSaveButton();
    }

    disconnect() {
        window.removeEventListener(
            'beforeunload',
            this.beforeUnloadHandler,
        );

        document.removeEventListener(
            'turbo:before-visit',
            this.beforeVisitHandler,
        );
    }

    submit() {
        this.hasUnsavedChanges = false;
    }

    selectStatus(event) {
        this.activeStatusValue = event.currentTarget.dataset.status;
        this.updateStatusButtons();
    }

    updateCell(event) {
        const cell = event.currentTarget;

        if (cell.dataset.blocked === 'true') {
            return;
        }

        const status = this.activeStatusValue;

        cell.dataset.status = status;

        this.applyCellAppearance(cell, status);
        this.updateHiddenInput(cell, status);
        this.updateSaveButton();
    }

    applyToAll(event) {
        const scope = event.currentTarget.dataset.scope;
        const status = this.activeStatusValue;

        this.cellTargets
            .filter((cell) => {
                if (cell.dataset.blocked === 'true') {
                    return false;
                }

                return scope === 'all'
                    || cell.dataset.period === scope;
            })
            .forEach((cell) => {
                cell.dataset.status = status;

                this.applyCellAppearance(cell, status);
                this.updateHiddenInput(cell, status);
            });

        this.updateSaveButton();
    }

    applyCellAppearance(cell, status) {
        cell.classList.remove(
            'is-empty',
            'is-available',
            'is-maybe',
            'is-unavailable',
        );

        const symbol = cell.querySelector(
            '.availability-cell__symbol',
        );

        if (!symbol) {
            return;
        }

        switch (status) {
            case 'available':
                cell.classList.add('is-available');
                cell.title = 'Disponible';
                symbol.textContent = '✓';
                break;

            case 'maybe':
                cell.classList.add('is-maybe');
                cell.title = 'Peut-être';
                symbol.textContent = '?';
                break;

            case 'unavailable':
                cell.classList.add('is-unavailable');
                cell.title = 'Indisponible';
                symbol.textContent = '×';
                break;

            default:
                cell.classList.add('is-empty');
                cell.title = 'Non renseigné';
                symbol.textContent = '—';
        }
    }

    updateHiddenInput(cell, status) {
        const slotId = cell.dataset.slotId;

        const input = this.inputTargets.find(
            (candidate) => candidate.dataset.slotId === slotId,
        );

        if (input) {
            input.value = status;
        }
    }

    updateStatusButtons() {
        this.statusButtonTargets.forEach((button) => {
            button.classList.toggle(
                'is-active',
                button.dataset.status === this.activeStatusValue,
            );
        });
    }

    updateSaveButton() {
        if (!this.hasSaveButtonTarget) {
            return;
        }

        const hasChanges = this.inputTargets.some(
            (input) => input.value !== input.dataset.initialValue,
        );

        this.hasUnsavedChanges = hasChanges;
        this.saveButtonTarget.disabled = !hasChanges;
    }
}
