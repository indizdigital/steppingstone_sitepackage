document.addEventListener('DOMContentLoaded', function () {

    // Kurze Quersumme (djb2) des Alert-Inhalts
    function hashString(str) {
        let h = 5381;
        for (let i = 0; i < str.length; i++) {
            h = ((h << 5) + h + str.charCodeAt(i)) | 0;
        }
        return (h >>> 0).toString(36);
    }

    function getCookie(name) {
        const match = document.cookie.match(new RegExp('(?:^|; )' + name + '=([^;]*)'));
        return match ? decodeURIComponent(match[1]) : null;
    }

    document.querySelectorAll('[data-alert-id]').forEach(function (alert) {

        const cookieName = 'alert_closed_' + alert.getAttribute('data-alert-id');
        const version = hashString(alert.textContent.replace(/\s+/g, ' ').trim());

        // Genau diese Version wurde bereits weggeklickt
        if (getCookie(cookieName) === version) {
            alert.remove();
            return;
        }

        alert.hidden = false;

        const btn = alert.querySelector('[data-alert-close]');
        if (!btn) return;

        btn.addEventListener('click', function () {

            // Cookie mit Inhalts-Version setzen (30 Tage gültig)
            const date = new Date();
            date.setTime(date.getTime() + (30*24*60*60*1000));

            document.cookie =
                cookieName + '=' + version + '; expires=' + date.toUTCString() +
                '; path=/; SameSite=Lax';

            alert.classList.add('js-is-closing');

            setTimeout(function () {
                alert.remove();
            }, 300);
        });
    });
});