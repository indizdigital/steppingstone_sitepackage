// Resources/Public/JavaScript/ndz-gallery.js
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.pswp-gallery').forEach(function (gallery) {
        const lightbox = new PhotoSwipeLightbox({
            gallery: gallery,
            children: 'a[data-pswp-width]',
            pswpModule: PhotoSwipe,
            showHideAnimationType: 'fade',
        });
        lightbox.init();
    });
});