import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = [
        'overlay',
        'panel',
        'step',
        'previousButton',
        'nextButton',
        'counter',
    ];

    static values = {
        autoOpen: Boolean,
        nextLabel: String,
        finishLabel: String,
    };

    connect() {
        this.currentStep = 0;
        this.highlightedElement = null;

        if (this.autoOpenValue) {
            this.open();
        }
    }

    open() {
        this.currentStep = 0;
        this.overlayTarget.hidden = false;
        document.body.classList.add('has-calendar-help');

        this.showStep();
    }

    close() {
        this.clearHighlight();
        this.overlayTarget.hidden = true;
        document.body.classList.remove('has-calendar-help');
    }

    next() {
        if (this.currentStep >= this.stepTargets.length - 1) {
            this.close();
            return;
        }

        this.currentStep++;
        this.showStep();
    }

    previous() {
        if (this.currentStep === 0) {
            return;
        }

        this.currentStep--;
        this.showStep();
    }

    showStep() {
        this.clearHighlight();

        this.stepTargets.forEach((step, index) => {
            step.hidden = index !== this.currentStep;
        });

        const step = this.stepTargets[this.currentStep];
        const selector = this.getHighlightSelector(step);

        if (selector) {
            const element = document.querySelector(selector);

            if (element) {
                element.classList.add('calendar-help-highlight');

                element.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center',
                });

                this.highlightedElement = element;
            }
        }

        this.counterTarget.textContent =
            `${this.currentStep + 1} / ${this.stepTargets.length}`;

        this.previousButtonTarget.disabled =
            this.currentStep === 0;

        this.nextButtonTarget.textContent =
            this.currentStep === this.stepTargets.length - 1
                ? this.finishLabelValue
                : `${this.nextLabelValue} →`;
    }

    getHighlightSelector(step) {
        const isMobile = window.matchMedia(
            '(max-width: 700px)',
        ).matches;

        return isMobile
            ? step.dataset.highlightMobile
            : step.dataset.highlightDesktop;
    }

    clearHighlight() {
        if (!this.highlightedElement) {
            return;
        }

        this.highlightedElement.classList.remove(
            'calendar-help-highlight',
        );

        this.highlightedElement = null;
    }
}
