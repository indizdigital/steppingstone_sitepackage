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
        document.querySelectorAll('.ndz-mainmenu-btn[aria-haspopup="true"]').forEach(b => {
            b.setAttribute('aria-expanded', 'false');
        });

        if (!isOpen) {
            submenu.classList.add('js-submenu-open');
            btn.setAttribute('aria-expanded', 'true');
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
        document.querySelectorAll('.ndz-mainmenu-btn[aria-haspopup="true"]').forEach(b => {
            b.setAttribute('aria-expanded', 'false');
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
        document.querySelectorAll('.ndz-mainmenu-btn[aria-haspopup="true"]').forEach(b => {
            b.setAttribute('aria-expanded', 'false');
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

function showEmails(){
    document.querySelectorAll("a[data-mailto-token][data-mailto-vector]").forEach(function(i){
        
        const n=i.dataset,o=n.mailtoToken,e=parseInt(n.mailtoVector,10)*-1;
        let em =r(o,e)
        if(i.querySelector(".js-encoded")){
         i.querySelector(".js-encoded").innerHTML = em.replace(/mailto:/,"")
        }

    });

    function r(t,i){let n="";for(let o=0;o<t.length;o++){const e=t.charCodeAt(o);e>=43&&e<=58?n+=c(e,43,58,i):e>=64&&e<=90?n+=c(e,64,90,i):e>=97&&e<=122?n+=c(e,97,122,i):n+=t.charAt(o)}return n}
    function c(t,i,n,o){return t=t+o,o>0&&t>n?t=i+(t-n-1):o<0&&t<i&&(t=n-(i-t-1)),String.fromCharCode(t)}
}
/* INIT HIGHLIGHTJS */
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('pre code').forEach(el => {
        hljs.highlightElement(el);
    });

    showEmails();
});

var gotoform_anchor = document.getElementById("gotoform");
if(gotoform_anchor){
    gotoform_anchor.href = "#" + document.querySelector(".frame-type-form_formframework").id;
}

/* COOKIE BANNER */
(function () {
    const banner = document.querySelector('[data-cookiebanner]');
    if (!banner) return;

    const COOKIE_NAME = 'ndz-cookie-consent';
    const COOKIE_MAX_AGE = 60 * 60 * 24 * 180; // 180 Tage
    const acceptAllBtn = banner.querySelector('[data-cookiebanner-accept-all]');
    const rejectBtn = banner.querySelector('[data-cookiebanner-reject]');
    const saveBtn = banner.querySelector('[data-cookiebanner-save]');
    const settingsToggle = banner.querySelector('[data-cookiebanner-settings-toggle]');
    const categoryInputs = banner.querySelectorAll('[data-cookiebanner-category]');
    const openTriggers = document.querySelectorAll('[data-cookiebanner-open]');

    function getCookie(name) {
        const match = document.cookie.match(new RegExp('(?:^|; )' + name + '=([^;]*)'));
        return match ? decodeURIComponent(match[1]) : null;
    }

    function setCookie(name, value, maxAge) {
        document.cookie = name + '=' + encodeURIComponent(value) + '; path=/; max-age=' + maxAge + '; SameSite=Lax';
    }

    function getConsent() {
        try {
            return JSON.parse(getCookie(COOKIE_NAME));
        } catch (e) {
            return null;
        }
    }

    function applyConsent(consent) {
        categoryInputs.forEach(input => {
            input.checked = input.disabled || !!consent[input.dataset.cookiebannerCategory];
        });
        document.dispatchEvent(new CustomEvent('ndz:cookieconsent', { detail: consent }));
    }

    function openBanner() {
        banner.classList.add('js-cookiebanner-open');
    }

    function closeBanner() {
        banner.classList.remove('js-cookiebanner-open', 'js-cookiebanner-settings-open');
        settingsToggle?.setAttribute('aria-expanded', 'false');
    }

    function saveConsent(consent) {
        consent.timestamp = new Date().toISOString();
        setCookie(COOKIE_NAME, JSON.stringify(consent), COOKIE_MAX_AGE);
        applyConsent(consent);
        closeBanner();
    }

    function collectCategories(forceAccept) {
        const consent = { necessary: true };
        categoryInputs.forEach(input => {
            const category = input.dataset.cookiebannerCategory;
            consent[category] = input.disabled ? true : (forceAccept || input.checked);
        });
        return consent;
    }

    acceptAllBtn?.addEventListener('click', () => {
        saveConsent(collectCategories(true));
    });

    rejectBtn?.addEventListener('click', () => {
        const consent = { necessary: true };
        categoryInputs.forEach(input => {
            consent[input.dataset.cookiebannerCategory] = false;
        });
        saveConsent(consent);
    });

    saveBtn?.addEventListener('click', () => {
        saveConsent(collectCategories(false));
    });

    settingsToggle?.addEventListener('click', () => {
        const isOpen = banner.classList.toggle('js-cookiebanner-settings-open');
        settingsToggle.setAttribute('aria-expanded', isOpen);
    });

    openTriggers.forEach(trigger => {
        trigger.addEventListener('click', (e) => {
            e.preventDefault();
            const consent = getConsent();
            if (consent) applyConsent(consent);
            openBanner();
        });
    });

    const existingConsent = getConsent();
    if (existingConsent) {
        applyConsent(existingConsent);
    } else {
        openBanner();
    }
})();