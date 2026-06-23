document.addEventListener('DOMContentLoaded', () => {
    const base = document.querySelector('.js-carousel')?.dataset.stoneBase;
    if (!base) return;
    document.querySelectorAll('.js-carousel-stone').forEach(async (container) => {
        const n = Math.floor(Math.random() * 7) + 1;
        const res = await fetch(`${base}stone-${n}.svg`);
        const svgText = await res.text();
        const doc = new DOMParser().parseFromString(svgText, 'image/svg+xml');
        const svg = doc.querySelector('svg');
        if (!svg) return;
        svg.classList.add('w-full', 'h-full', 'fill-white');
        container.appendChild(svg);
    });
});