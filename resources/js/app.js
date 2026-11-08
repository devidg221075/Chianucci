import Alpine from 'alpinejs';

window.Alpine = Alpine;

// Avviso cookie: ricorda la chiusura nel browser; se lo storage non è disponibile, resta visibile.
Alpine.data('cookieNotice', () => ({
    show: false,
    init() {
        try { this.show = !localStorage.getItem('cookie-notice'); } catch (e) { this.show = true; }
    },
    dismiss() {
        try { localStorage.setItem('cookie-notice', '1'); } catch (e) { /* niente storage */ }
        this.show = false;
    },
}));
Alpine.start();
