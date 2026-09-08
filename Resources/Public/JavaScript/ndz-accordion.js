const accordionItems = document.querySelectorAll('.js-accordion-item');

accordionItems.forEach((item, index) => {
    const header = item.querySelector('.js-accordion-header');
    const content = item.querySelector('.js-accordion-content');

    const headerId = `accordion-header-${index + 1}`;
    const contentId = `accordion-content-${index + 1}`;

    header.setAttribute('id', headerId);
    header.setAttribute('aria-controls', contentId);

    content.setAttribute('id', contentId);
    content.setAttribute('role', 'region');
    content.setAttribute('aria-labelledby', headerId);

    if (item.classList.contains('js-accordion-active')) {
        header.setAttribute('aria-expanded', 'true');
    } else {
        header.setAttribute('aria-expanded', 'false');
    }

    header.addEventListener('click', () => {

        const isOpen = item.classList.contains('js-accordion-active');

        item.classList.toggle('js-accordion-active', !isOpen);
        header.setAttribute('aria-expanded', isOpen ? 'false' : 'true');

        if (!isOpen) {
            const anchor = item.querySelector('a[id]');
            if (anchor && anchor.id) {
                history.replaceState(null, '', '#' + anchor.id);
            }
        }
    });
});

function openAccordionFromHash() {
    if (!location.hash || location.hash.length < 2) return;
    let target;
    try {
        target = document.querySelector(location.hash);
    } catch (e) {
        return;
    }
    if (!target) return;
    const item = target.closest('.js-accordion-item');
    if (!item || item.classList.contains('js-accordion-active')) return;
    const header = item.querySelector('.js-accordion-header');
    if (header) header.click();
}
openAccordionFromHash();
window.addEventListener('hashchange', openAccordionFromHash);