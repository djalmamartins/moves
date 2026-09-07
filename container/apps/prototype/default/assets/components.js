(() => {
    const root = document.documentElement;
    const themeToggle = document.querySelector('[data-theme-toggle]');
    const modal = document.querySelector('[data-modal]');
    const toast = document.querySelector('[data-toast]');
    let toastTimer;

    themeToggle?.addEventListener('click', () => {
        const dark = root.dataset.theme !== 'dark';
        root.dataset.theme = dark ? 'dark' : 'light';
        themeToggle.setAttribute('aria-pressed', String(dark));
    });

    document.querySelector('[data-modal-open]')?.addEventListener('click', () => modal?.showModal());
    document.querySelectorAll('[data-modal-close]').forEach((button) => button.addEventListener('click', () => modal?.close()));

    document.querySelector('[data-toast-open]')?.addEventListener('click', () => {
        if (!toast) return;
        toast.classList.add('is-visible');
        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => toast.classList.remove('is-visible'), 2800);
    });

    document.querySelectorAll('[role="tablist"]').forEach((tablist) => {
        const tabs = [...tablist.querySelectorAll('[role="tab"]')];
        tablist.addEventListener('keydown', (event) => {
            if (!['ArrowLeft', 'ArrowRight'].includes(event.key)) return;
            event.preventDefault();
            const current = tabs.indexOf(document.activeElement);
            tabs[(current + (event.key === 'ArrowRight' ? 1 : -1) + tabs.length) % tabs.length].focus();
        });
        tabs.forEach((tab) => tab.addEventListener('click', () => {
            tabs.forEach((item) => {
                const selected = item === tab;
                item.setAttribute('aria-selected', String(selected));
                item.tabIndex = selected ? 0 : -1;
                document.getElementById(item.getAttribute('aria-controls')).hidden = !selected;
            });
        }));
    });
})();
