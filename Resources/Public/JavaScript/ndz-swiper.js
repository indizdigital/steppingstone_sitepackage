document.querySelectorAll(".swiper").forEach(el => {
    const d = el.dataset;
    const navScope = el.parentElement;
    const prevEl = navScope.querySelector(".swiper-button-prev");
    const nextEl = navScope.querySelector(".swiper-button-next");

    new Swiper(el, {
        speed: 1000,
        slidesPerView: d.slidesPerView ?? "auto",
        spaceBetween: Number(d.spaceBetween ?? 20),
        loop: d.loop === "true",
        centeredSlides: d.centeredSlides === "true",
        slidesOffsetBefore: Number(d.slidesOffsetBefore ?? 0),
        initialSlide: Number(d.initialSlide ?? 0),

        ...(d.autoplay && {
            autoplay: { delay: Number(d.autoplay), disableOnInteraction: false }
        }),

        pagination: el.querySelector(".swiper-pagination") ? {
            el: ".swiper-pagination",
            dynamicBullets: d.dynamicBullets !== "false",
            clickable: true,
        } : false,

        navigation: nextEl ? {
            nextEl,
            prevEl,
            addIcons: false,
            enabled: false,  // standardmässig aus
        } : false,

        scrollbar: el.querySelector(".swiper-scrollbar") ? {
            el: ".swiper-scrollbar",
        } : false,

        breakpoints: {
            1280: {
                navigation: { enabled: true },
            }
        },
    });
});
