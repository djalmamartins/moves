(() => {
    const sidebar = document.querySelector('[data-sidebar]');
    const backdrop = document.querySelector('[data-backdrop]');
    const filter = document.querySelector('[data-portfolio-filter]');
    const toast = document.querySelector('[data-toast]');
    let toastTimer;

    const closeMenu = () => {
        sidebar?.classList.remove('is-open');
        backdrop?.classList.remove('is-visible');
        document.querySelector('[data-menu-toggle]')?.setAttribute('aria-expanded', 'false');
    };
    const notify = (message) => {
        if (!toast) return;
        toast.textContent = message;
        toast.classList.add('is-visible');
        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => toast.classList.remove('is-visible'), 2800);
    };

    document.querySelector('[data-menu-toggle]')?.addEventListener('click', (event) => {
        const open = !sidebar?.classList.contains('is-open');
        sidebar?.classList.toggle('is-open', open);
        backdrop?.classList.toggle('is-visible', open);
        event.currentTarget.setAttribute('aria-expanded', String(open));
    });
    backdrop?.addEventListener('click', closeMenu);
    document.addEventListener('keydown', (event) => { if (event.key === 'Escape') closeMenu(); });

    filter?.addEventListener('change', () => {
        const selected = filter.options[filter.selectedIndex].text;
        document.querySelector('[data-scope-label]').textContent = selected;
        document.querySelector('[data-condominiums]').textContent = filter.value === 'all' ? '22' : '8';
        notify(`Visão atualizada: ${selected}.`);
    });
    document.querySelectorAll('[data-demo-action]').forEach((button) => button.addEventListener('click', () => notify(button.dataset.demoAction)));
})();
