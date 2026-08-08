import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = {
        value: String,
    };

    async copy() {
        try {
            await navigator.clipboard.writeText(
                this.valueValue,
            );

            const original = this.element.textContent;

            this.element.textContent = '✓ Copié';

            setTimeout(() => {
                this.element.textContent = original;
            }, 2000);
        } catch {
            prompt(
                'Copiez ce lien :',
                this.valueValue,
            );
        }
    }
}
