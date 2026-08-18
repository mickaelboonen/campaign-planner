import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = {
        value: String,
        copiedLabel: String,
        fallbackPrompt: String,
    };

    async copy() {
        try {
            await navigator.clipboard.writeText(this.valueValue);

            const original = this.element.textContent;

            this.element.textContent = `✓ ${this.copiedLabelValue}`;

            setTimeout(() => {
                this.element.textContent = original;
            }, 2000);
        } catch {
            prompt(
                this.fallbackPromptValue,
                this.valueValue,
            );
        }
    }
}
