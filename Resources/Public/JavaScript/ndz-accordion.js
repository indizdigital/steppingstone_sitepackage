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
    });
});