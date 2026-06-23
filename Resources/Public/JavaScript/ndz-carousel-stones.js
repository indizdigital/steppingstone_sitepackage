document.addEventListener('DOMContentLoaded', () => {
    const carousel = document.querySelector('.js-carousel-stones');
    if (carousel && window.Flickity) {
        const initialSlide = parseInt(carousel.dataset.initialSlide || '1', 10) - 1;
        new window.Flickity(carousel, {
            cellSelector: '.js-carousel-cell',
            contain: true,
            wrapAround: true,
            pageDots: true,
            cellAlign: 'left',
            groupCells: false,
            initialIndex: initialSlide >= 0 ? initialSlide : 0
        });
    }
});