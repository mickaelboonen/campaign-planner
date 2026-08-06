import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = [
        'slot',
        'input',
        'modeButton',
        'saveButton',
    ];

    static values = {
        activeMode: {
            type: String,
            default: 'blocked',
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

        this.updateModeButtons();
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

    selectMode(event) {
        this.activeModeValue = event.currentTarget.dataset.mode;
        this.updateModeButtons();
    }

    updateSlot(event) {
        const slot = event.currentTarget;
        const status = this.activeModeValue;

        this.applySlotState(slot, status);
        this.updateHiddenInput(slot.dataset.slotId, status);
        this.updateSaveButton();
    }

    applyShortcut(event) {
        const scope = event.currentTarget.dataset.scope;
        const status = this.activeModeValue;

        this.slotTargets
            .filter((slot) => {
                return scope === 'all'
                    || slot.dataset.period === scope;
            })
            .forEach((slot) => {
                this.applySlotState(slot, status);
                this.updateHiddenInput(slot.dataset.slotId, status);
            });

        this.updateSaveButton();
    }

    applySlotState(slot, status) {
        slot.dataset.status = status;

        slot.classList.toggle(
            'is-blocked',
            status === 'blocked',
        );

        slot.classList.toggle(
            'is-open',
            status === 'open',
        );

        const label = slot.querySelector(
            '.availability-table__period-label',
        );

        if (label) {
            label.textContent = status === 'blocked'
                ? 'Bloqué'
                : slot.dataset.label;
        }

        this.element
            .querySelectorAll(
                `[data-calendar-slot-id="${slot.dataset.slotId}"]`,
            )
            .forEach((cell) => {
                cell.classList.toggle(
                    'is-blocked',
                    status === 'blocked',
                );
            });
    }

    updateHiddenInput(slotId, status) {
        const input = this.inputTargets.find(
            (candidate) => candidate.dataset.slotId === slotId,
        );

        if (input) {
            input.value = status;
        }
    }

    updateModeButtons() {
        this.modeButtonTargets.forEach((button) => {
            button.classList.toggle(
                'is-active',
                button.dataset.mode === this.activeModeValue,
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