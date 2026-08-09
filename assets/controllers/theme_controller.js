import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = [
        'icon',
        'label',
        'button',
    ];

    connect() {
        const savedTheme =
            localStorage.getItem('theme') ?? 'dark';

        this.applyTheme(savedTheme);
    }

    toggle() {
        const currentTheme =
            document.documentElement.dataset.theme ?? 'dark';

        const nextTheme =
            currentTheme === 'dark'
                ? 'light'
                : 'dark';

        this.applyTheme(nextTheme);

        localStorage.setItem('theme', nextTheme);
    }

    applyTheme(theme) {
        document.documentElement.dataset.theme = theme;

        const isLight = theme === 'light';

        this.iconTarget.textContent =
            isLight ? '☾' : '☀';

        this.labelTarget.textContent =
            isLight ? 'Thème sombre' : 'Thème clair';

        this.buttonTarget.setAttribute(
            'aria-checked',
            String(isLight),
        );
    }
}
