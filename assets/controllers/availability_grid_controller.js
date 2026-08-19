import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['cell', 'statusButton', 'input', 'saveStatus'];

    static values = {
        activeStatus: {
            type: String,
            default: 'available',
        },
        autosaveUrl: String,
        availableLabel: String,
        maybeLabel: String,
        unavailableLabel: String,
        unansweredLabel: String,
        savingLabel: String,
        savedLabel: String,
        errorLabel: String,
        unsavedChangesMessage: String,
    };

    connect() {
        this.pendingChanges = new Map();
        this.savedValues = new Map();
        this.saveTimeout = null;
        this.savePromise = null;
        this.allowNavigation = false;

        this.inputTargets.forEach((input) => {
            this.savedValues.set(input.dataset.slotId, input.value);
        });

        this.beforeUnloadHandler = (event) => {
            if (!this.hasPendingChanges()) {
                return;
            }

            event.preventDefault();
            event.returnValue = '';
        };

        this.beforeVisitHandler = async (event) => {
            if (this.allowNavigation || !this.hasPendingChanges()) {
                return;
            }

            event.preventDefault();

            const url = event.detail?.url;

            try {
                await this.flush();

                if (url) {
                    this.allowNavigation = true;
                    window.location.assign(url);
                }
            } catch {
                // Les modifications restent en attente.
            }
        };

        window.addEventListener('beforeunload', this.beforeUnloadHandler);
        document.addEventListener('turbo:before-visit', this.beforeVisitHandler);

        this.updateStatusButtons();
        this.updateSaveStatus();
    }

    disconnect() {
        clearTimeout(this.saveStatusTimeout);

        window.removeEventListener('beforeunload', this.beforeUnloadHandler);
        document.removeEventListener('turbo:before-visit', this.beforeVisitHandler);
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

        const status = this.getNextStatus(cell.dataset.status ?? '');

        this.flipCellToStatus(cell, status);
    }

    flipCellToStatus(cell, status) {
        if (
            cell.classList.contains('is-flipping-out')
            || cell.classList.contains('is-flipping-in')
        ) {
            return;
        }

        cell.classList.add('is-flipping-out');

        window.setTimeout(() => {
            this.setCellStatus(cell, status);
            this.scheduleSave();

            cell.classList.remove('is-flipping-out');
            cell.classList.add('is-flipping-in');

            requestAnimationFrame(() => {
                requestAnimationFrame(() => {
                    cell.classList.remove('is-flipping-in');
                });
            });
        }, 220);
    }

    applyToAll(event) {
        const scope = event.currentTarget.dataset.scope;
        const status = this.activeStatusValue;

        this.cellTargets
            .filter((cell) => {
                if (cell.dataset.blocked === 'true') {
                    return false;
                }

                return scope === 'all' || cell.dataset.period === scope;
            })
            .forEach((cell) => {
                this.setCellStatus(cell, status);
            });

        this.scheduleSave();
    }

    getNextStatus(status) {
        switch (status) {
            case '':
                return 'available';
            case 'available':
                return 'maybe';
            case 'maybe':
                return 'unavailable';
            default:
                return '';
        }
    }

    setCellStatus(cell, status) {
        cell.dataset.status = status;

        this.applyCellAppearance(cell, status);
        this.updateHiddenInput(cell, status);
        this.trackChange(cell.dataset.slotId, status);
    }

    trackChange(slotId, status) {
        const savedValue = this.savedValues.get(slotId) ?? '';

        if (status === savedValue) {
            this.pendingChanges.delete(slotId);
        } else {
            this.pendingChanges.set(slotId, status);
        }
    }

    scheduleSave() {
        clearTimeout(this.saveTimeout);

        if (this.pendingChanges.size === 0) {
            this.updateSaveStatus('saved');
            return;
        }

        this.updateSaveStatus('saving');

        this.saveTimeout = setTimeout(() => {
            this.flush().catch(() => {});
        }, 1000);
    }

    async flush() {
        clearTimeout(this.saveTimeout);

        if (this.savePromise) {
            await this.savePromise;

            if (this.pendingChanges.size === 0) {
                return;
            }
        }

        if (this.pendingChanges.size === 0) {
            this.updateSaveStatus('saved');
            return;
        }

        const changes = Object.fromEntries(this.pendingChanges);
        const token = this.element.querySelector('[name="_token"]')?.value;
        const week = this.element.querySelector('[name="week"]')?.value;

        this.updateSaveStatus('saving');

        this.savePromise = fetch(this.autosaveUrlValue, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                _token: token,
                week,
                availabilities: changes,
            }),
        })
            .then(async (response) => {
                if (!response.ok) {
                    throw new Error(`Autosave failed with status ${response.status}`);
                }

                return response.json();
            })
            .then(() => {
                Object.entries(changes).forEach(([slotId, status]) => {
                    this.savedValues.set(slotId, status);

                    if (this.pendingChanges.get(slotId) === status) {
                        this.pendingChanges.delete(slotId);
                    }

                    const input = this.inputTargets.find(
                        (candidate) => candidate.dataset.slotId === slotId,
                    );

                    if (input) {
                        input.dataset.initialValue = status;
                    }
                });

                this.updateSaveStatus(
                    this.pendingChanges.size === 0
                        ? 'saved'
                        : 'saving',
                );
            })
            .catch((error) => {
                this.updateSaveStatus('error');
                throw error;
            })
            .finally(() => {
                this.savePromise = null;

                if (this.pendingChanges.size > 0) {
                    this.scheduleSave();
                }
            });

        return this.savePromise;
    }

    hasPendingChanges() {
        return this.pendingChanges.size > 0 || this.savePromise !== null;
    }

    applyCellAppearance(cell, status) {
        cell.classList.remove(
            'is-empty',
            'is-available',
            'is-maybe',
            'is-unavailable',
        );

        const symbol = cell.querySelector('.availability-cell__symbol');

        if (!symbol) {
            return;
        }

        switch (status) {
            case 'available':
                cell.classList.add('is-available');
                cell.title = this.availableLabelValue;
                symbol.textContent = '✓';
                break;

            case 'maybe':
                cell.classList.add('is-maybe');
                cell.title = this.maybeLabelValue;
                symbol.textContent = '?';
                break;

            case 'unavailable':
                cell.classList.add('is-unavailable');
                cell.title = this.unavailableLabelValue;
                symbol.textContent = '×';
                break;

            default:
                cell.classList.add('is-empty');
                cell.title = this.unansweredLabelValue;
                symbol.textContent = '—';
        }
    }

    updateHiddenInput(cell, status) {
        const input = this.inputTargets.find(
            (candidate) => candidate.dataset.slotId === cell.dataset.slotId,
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

    updateSaveStatus(status) {
        if (!this.hasSaveStatusTarget) {
            return;
        }

        clearTimeout(this.saveStatusTimeout);

        this.saveStatusTarget.classList.remove(
            'is-saving',
            'is-saved',
            'is-error',
            'is-hidden',
        );

        switch (status) {
            case 'saving':
                this.saveStatusTarget.classList.add('is-saving');
                this.saveStatusTarget.textContent = this.savingLabelValue;
                break;

            case 'error':
                this.saveStatusTarget.classList.add('is-error');
                this.saveStatusTarget.textContent = this.errorLabelValue;
                break;

            case 'saved':
                this.saveStatusTarget.classList.add('is-saved');
                this.saveStatusTarget.textContent = this.savedLabelValue;

                this.saveStatusTimeout = window.setTimeout(() => {
                    this.saveStatusTarget.classList.add('is-hidden');
                }, 1500);
                break;

            default:
                this.saveStatusTarget.textContent = '';
        }
    }
}
