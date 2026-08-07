import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = [
        'sidebar',
        'overlay',
    ];

    connect() {
        this.escapeHandler = this.handleEscape.bind(this);

        document.addEventListener(
            'keydown',
            this.escapeHandler,
        );
    }

    disconnect() {
        document.removeEventListener(
            'keydown',
            this.escapeHandler,
        );
    }

    open() {
        this.sidebarTarget.classList.add('is-open');
        this.overlayTarget.classList.add('is-visible');
        document.body.classList.add('menu-open');
    }

    close() {
        this.sidebarTarget.classList.remove('is-open');
        this.overlayTarget.classList.remove('is-visible');
        document.body.classList.remove('menu-open');
    }

    toggle() {
        if (this.sidebarTarget.classList.contains('is-open')) {
            this.close();

            return;
        }

        this.open();
    }

    handleEscape(event) {
        if (event.key === 'Escape') {
            this.close();
        }
    }
}
