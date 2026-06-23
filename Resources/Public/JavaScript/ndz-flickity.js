const carousels = [];

document.querySelectorAll('.js-carousel').forEach(carousel => {
    carousels.push(new Flickity(carousel, {
        cellAlign: 'left',
        contain: true,
        wrapAround: true,
        //groupCells: 3
    }));
});

let resizeTimer;
window.addEventListener('resize', () => {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(() => {
        carousels.forEach(flkty => {
            flkty.viewport.style.height = '';
            flkty.resize();
        });
    }, 150);
});