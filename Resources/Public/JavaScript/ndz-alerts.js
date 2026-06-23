document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll('[data-alert-id]').forEach(function (alert) {

        const id = alert.getAttribute('data-alert-id');
        const cookieName = 'alert_closed_' + id;

        // Button finden
        const btn = alert.querySelector('[data-alert-close]');

        if (!btn) return;

        btn.addEventListener('click', function () {

            // Cookie setzen (30 Tage gültig)
            const date = new Date();
            date.setTime(date.getTime() + (30*24*60*60*1000));

            document.cookie =
                cookieName + '=1; expires=' + date.toUTCString() +
                '; path=/; SameSite=Lax';

            alert.classList.add('js-is-closing');

            setTimeout(function () {
                alert.remove();
            }, 300);
        });

    });

});