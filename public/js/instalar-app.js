/**
 * Instalar Vital Clean como app (PWA) — clonado del mismo patrón que ya
 * usa Reportes 311 Consultores, adaptado a JS sin framework:
 *
 *   - En Android/Chrome/Edge, el navegador dispara "beforeinstallprompt";
 *     lo capturamos para ofrecer un botón "Instalar" propio en vez del
 *     mini-banner genérico del navegador.
 *   - En iPhone/iPad (Safari) ese evento no existe — ahí se muestran las
 *     instrucciones manuales ("Compartir" → "Agregar a pantalla de
 *     inicio").
 *   - Si el navegador no ofrece ninguna de las dos cosas (o ya se
 *     instaló, o ya se cerró el aviso antes en este dispositivo), no se
 *     muestra nada.
 */
var InstalarApp = (function () {
    var CLAVE_DESCARTADO = 'vc_banner_instalar_off';
    var eventoDiferido = null;

    function esStandalone() {
        return window.matchMedia('(display-mode: standalone)').matches || navigator.standalone === true;
    }

    function esIOS() {
        var ua = navigator.userAgent;
        return /iphone|ipad|ipod/i.test(ua) || (/macintosh/i.test(ua) && navigator.maxTouchPoints > 1);
    }

    function esMovil() {
        return window.matchMedia('(pointer: coarse)').matches && window.innerWidth < 1100;
    }

    function descartado() {
        try {
            return localStorage.getItem(CLAVE_DESCARTADO) === '1';
        } catch (e) {
            return false;
        }
    }

    function descartar() {
        try { localStorage.setItem(CLAVE_DESCARTADO, '1'); } catch (e) { /* no crítico */ }
        var banner = document.getElementById('banner-instalar-app');
        if (banner) banner.style.display = 'none';
    }

    function actualizarBanner() {
        var banner = document.getElementById('banner-instalar-app');
        if (!banner) return;

        if (descartado() || esStandalone() || !esMovil()) {
            banner.style.display = 'none';
            return;
        }

        var textoIOS = banner.querySelector('[data-ios]');
        var textoGenerico = banner.querySelector('[data-generico]');
        var botonInstalar = banner.querySelector('[data-instalar]');

        if (esIOS()) {
            if (textoIOS) textoIOS.style.display = 'block';
            if (textoGenerico) textoGenerico.style.display = 'none';
            if (botonInstalar) botonInstalar.style.display = 'none';
        } else if (eventoDiferido) {
            if (textoIOS) textoIOS.style.display = 'none';
            if (textoGenerico) textoGenerico.style.display = 'none';
            if (botonInstalar) botonInstalar.style.display = 'inline-block';
        } else {
            if (textoIOS) textoIOS.style.display = 'none';
            if (textoGenerico) textoGenerico.style.display = 'block';
            if (botonInstalar) botonInstalar.style.display = 'none';
        }

        banner.style.display = 'flex';
    }

    function instalar() {
        if (!eventoDiferido) return;
        var evento = eventoDiferido;
        evento.prompt();
        evento.userChoice.catch(function () { /* el usuario cerró el diálogo sin elegir */ }).then(function () {
            eventoDiferido = null;
            descartar();
        });
    }

    function iniciar() {
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw.js', { scope: '/' }).catch(function () { /* no crítico */ });
        }

        window.addEventListener('beforeinstallprompt', function (e) {
            e.preventDefault();
            eventoDiferido = e;
            actualizarBanner();
        });
        window.addEventListener('appinstalled', function () {
            eventoDiferido = null;
            descartar();
        });

        actualizarBanner();
    }

    return { iniciar: iniciar, descartar: descartar, instalar: instalar };
})();
