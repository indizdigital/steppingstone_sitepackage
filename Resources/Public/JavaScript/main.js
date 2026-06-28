// import Flickity from './flickity.pkgd.js';

const nav = document.querySelector('[data-nav]');
const openBtn = document.querySelector('[data-nav-open]');
const search = document.querySelector('[data-search]');
const searchOpenBtn = document.querySelector('[data-search-open]');
const toggles = document.querySelectorAll('[data-submenu-toggle]');
const submenus = document.querySelectorAll('[data-submenu]');

function updateBodyScroll() {
    const isOpen = search.classList.contains('js-search-open') || nav.classList.contains('js-mobilenav-open');
    document.body.classList.toggle('overflow-hidden', isOpen);
}

function resetSubmenus() {
    submenus.forEach(submenu => {
        submenu.style.maxHeight = null;
        submenu.classList.remove('js-submenu-open');
    });

    toggles.forEach(toggle => {
        toggle.classList.remove('js-menu-btn-active');
    });
}

searchOpenBtn.addEventListener('click', () => {
    const isOpen = search.classList.toggle('js-search-open');
    searchOpenBtn.classList.toggle('js-search-active', isOpen);
    if (isOpen) {
        if (nav.classList.contains('js-mobilenav-open')) {
            nav.classList.remove('js-mobilenav-open');
            openBtn.classList.remove('js-mobilenav-active');
            resetSubmenus();
        }
        search.querySelector('input')?.focus();
    }
    updateBodyScroll();
});

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && search.classList.contains('js-search-open')) {
        search.classList.remove('js-search-open');
        searchOpenBtn.classList.remove('js-search-active');
        updateBodyScroll();
    }
});

openBtn.addEventListener('click', () => {
    nav.classList.toggle('js-mobilenav-open');
    openBtn.classList.toggle('js-mobilenav-active');

    if (nav.classList.contains('js-mobilenav-open')) {
        search.classList.remove('js-search-open');
        searchOpenBtn.classList.remove('js-search-active');
    } else {
        resetSubmenus();
    }
    updateBodyScroll();
});

toggles.forEach(toggle => {
    toggle.addEventListener('click', () => {
        const parent = toggle.closest('li');
        const submenu = parent.querySelector('[data-submenu]');

        if (submenu.style.maxHeight) {
            // schliessen
            submenu.style.maxHeight = null;
        } else {
            // öffnen (dynamisch!)
            submenu.style.maxHeight = submenu.scrollHeight + 'px';
        }

        toggle.classList.toggle('js-menu-btn-active');
    });
});

// Desktop: Klick auf Hauptmenü-Punkt öffnet Submenü
document.querySelectorAll('.ndz-mainmenu-btn').forEach(btn => {
    btn.addEventListener('click', (e) => {
        if (window.innerWidth < 1280) return;

        const item = btn.closest('.ndz-mainmenu-item');
        const submenu = item?.querySelector('[data-submenu]');
        if (!submenu) return;

        e.preventDefault();

        const isOpen = submenu.classList.contains('js-submenu-open');

        document.querySelectorAll('.ndz-submenu.js-submenu-open').forEach(s => {
            s.classList.remove('js-submenu-open');
        });

        if (!isOpen) {
            submenu.classList.add('js-submenu-open');
        }
    });
});

// Desktop: Submenü schliessen bei Klick ausserhalb
document.addEventListener('click', (e) => {
    if (window.innerWidth < 1280) return;
    if (!e.target.closest('.ndz-mainmenu-item')) {
        document.querySelectorAll('.ndz-submenu.js-submenu-open').forEach(s => {
            s.classList.remove('js-submenu-open');
        });
    }
});

// optional: bei Resize neu berechnen
window.addEventListener('resize', () => {
    document.querySelectorAll('[data-submenu]').forEach(submenu => {
        if (submenu.style.maxHeight) {
            submenu.style.maxHeight = submenu.scrollHeight + 'px';
        }
    });

    if (window.innerWidth < 1280) {
        document.querySelectorAll('.ndz-submenu.js-submenu-open').forEach(s => {
            s.classList.remove('js-submenu-open');
        });
    }
});

const mql = window.matchMedia('(min-width: 1280px)');

function updateLabels(e) {
  const isDesktop = e.matches;

  document.querySelectorAll('.js-lang-select option').forEach(opt => {
    opt.textContent = isDesktop
      ? opt.dataset.short
      : opt.dataset.full;
  });
}

// Initial ausführen
updateLabels(mql);

// Reagiert nur beim Breakpoint-Wechsel
mql.addEventListener('change', updateLabels);


const header = document.querySelector('[data-header]');
let lastScrollY = window.scrollY;
window.addEventListener('scroll', () => {
    const currentScrollY = window.scrollY;
    if (currentScrollY > lastScrollY && currentScrollY > 100) {
        // nach unten scrollen → Header klein
        header.classList.add('js-header-small');
    } else {
        // nach oben scrollen → Header normal
        header.classList.remove('js-header-small');
    }
    lastScrollY = currentScrollY;
});


/* INIT HIGHLIGHTJS */
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('pre code').forEach(el => {
        hljs.highlightElement(el);
    });
});