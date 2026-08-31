document.querySelectorAll('.js-flipbox-back').forEach((back) => {
    back.addEventListener('click', (event) => {
        if (event.target.closest('a')) {
            return;
        }

        const flipbox = back.closest('.js-flipbox');
        const checkbox = flipbox.querySelector('.js-flipbox-check');

        checkbox.checked = false;
    });
});