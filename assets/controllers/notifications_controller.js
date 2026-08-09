import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['badge'];

    static values = {
        markSeenUrl: String,
        unseenCount: Number,
    };

    async toggle() {
        if (!this.element.open || this.unseenCountValue === 0) {
            return;
        }

        const response = await fetch(this.markSeenUrlValue, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        if (!response.ok) {
            return;
        }

        this.unseenCountValue = 0;

        if (this.hasBadgeTarget) {
            this.badgeTarget.remove();
        }
    }

    close() {
        this.element.open = false;
    }
}
