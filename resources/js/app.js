import Alpine from 'alpinejs';

document.documentElement.classList.add('js'); // also set in <head>; kept for safety

/* Theme toggle. The initial theme is set by an inline script in <head> to avoid a flash. */
window.toggleTheme = () => {
    const root = document.documentElement;
    const current = root.dataset.theme || (matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
    const next = current === 'dark' ? 'light' : 'dark';
    root.dataset.theme = next;
    try { localStorage.setItem('theme', next); } catch (e) { /* storage blocked: the choice lasts this page only */ }
};

/* Scroll reveal */
const reveal = () => {
    const items = document.querySelectorAll('[data-reveal]');
    if (!('IntersectionObserver' in window)) {
        items.forEach((el) => el.classList.add('is-visible'));
        return;
    }
    const io = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                io.unobserve(entry.target);
            }
        });
    }, { rootMargin: '0px 0px -8% 0px' });
    items.forEach((el) => io.observe(el));
    // Safety net: never leave content hidden if the observer is slow or throttled.
    setTimeout(() => items.forEach((el) => el.classList.add('is-visible')), 1800);
};

/*
 * Kanban board (proposals and recruitment): drag a card to another column and the
 * new status is PATCHed. On failure the card goes back and a toast explains.
 */
const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';
Alpine.data('pipeline', () => ({
    dragging: null,
    over: null,
    toast: '',
    start(e, id) {
        this.dragging = id;
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/plain', String(id));
    },
    end() { this.dragging = null; this.over = null; },
    async drop(stage) {
        const id = this.dragging;
        this.over = null;
        if (!id) return;
        const card = document.getElementById('card-' + id);
        const from = card?.closest('[data-stage]')?.dataset.stage;
        const list = document.querySelector(`[data-stage="${stage}"] [data-list]`);
        if (!card || !list || from === stage) { this.dragging = null; return; }
        list.prepend(card);
        this.recount();
        try {
            const r = await fetch(card.dataset.url, {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrf(), 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify({ status: stage }),
            });
            if (!r.ok) throw new Error('Request failed');
            const d = await r.json();
            if (stage !== 'new') card.dataset.overdue = 'false';
            this.flash(`${card.dataset.name} moved to ${d.label}`);
        } catch (err) {
            document.querySelector(`[data-stage="${from}"] [data-list]`)?.prepend(card);
            this.recount();
            this.flash('Could not update. Please refresh and try again.');
        }
        this.dragging = null;
    },
    recount() {
        document.querySelectorAll('[data-stage]').forEach((col) => {
            const n = col.querySelectorAll('[data-list] > article').length;
            col.querySelector('[data-count]').textContent = n;
            col.querySelector('[data-empty]')?.classList.toggle('hidden', n > 0);
        });
    },
    flash(msg) { this.toast = msg; clearTimeout(this._t); this._t = setTimeout(() => { this.toast = ''; }, 2600); },
}));

/* Column chart hover: one tooltip that follows the focused or hovered column. */
Alpine.data('columnChart', () => ({
    tip: null,
    show(el) {
        const box = el.getBoundingClientRect();
        const host = this.$root.getBoundingClientRect();
        this.tip = { label: el.dataset.label, value: el.dataset.value, x: box.left - host.left + box.width / 2, y: box.top - host.top };
    },
    hide() { this.tip = null; },
}));

/* Dashboard chrome: density, mobile nav, "/" to search. */
Alpine.data('adminShell', () => ({
    nav: false,
    density: (() => { try { return localStorage.getItem('density') || 'comfortable'; } catch (e) { return 'comfortable'; } })(),
    init() {
        this.$el.dataset.density = this.density;
        window.addEventListener('keydown', (e) => {
            const typing = ['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement?.tagName);
            if (e.key === '/' && !typing) {
                const search = document.querySelector('[data-search]');
                if (search) { e.preventDefault(); search.focus(); }
            }
        });
    },
    toggleDensity() {
        this.density = this.density === 'compact' ? 'comfortable' : 'compact';
        this.$el.dataset.density = this.density;
        try { localStorage.setItem('density', this.density); } catch (e) { /* ignore */ }
    },
}));

window.Alpine = Alpine;
Alpine.start();
if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', reveal);
else reveal();
