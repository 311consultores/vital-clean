/**
 * Envío resiliente a conexión intermitente — para las dos pantallas donde
 * el vendedor visita al cliente en campo (Nuevo Pedido y Cierre de
 * Entrega): si el envío falla por falta de señal justo al firmar, se
 * guarda en la tableta y se reintenta solo (al volver la señal, al cargar
 * cualquier pantalla del sistema, o cada cierto tiempo), sin que el
 * vendedor tenga que quedarse parado ahí — puede seguir manejando a la
 * siguiente entrega.
 *
 * No usa Service Worker/Background Sync a propósito: esta app no tiene
 * páginas offline precargadas (abrir el folio del siguiente cliente
 * siempre necesita conexión, igual que hoy), así que reintentar al cargar
 * cualquier pantalla ya cubre el caso real — un bache momentáneo de señal,
 * no un tramo muerto de horas — con mucho menos riesgo que meter una
 * pieza nueva de infraestructura (Service Worker) a una tableta en campo.
 *
 * El servidor deduplica por idempotency_key (ver App\Support\Idempotencia)
 * para que un reintento cuya respuesta original se perdió no duplique el
 * pedido/la entrega.
 */
var EnvioResiliente = (function () {
    var CLAVE_COLA = 'vc_envios_pendientes';
    var CLAVE_SINCRONIZADO = 'vc_ultimo_sincronizado';

    function leerCola() {
        try {
            return JSON.parse(localStorage.getItem(CLAVE_COLA) || '[]');
        } catch (e) {
            return [];
        }
    }

    function guardarCola(cola) {
        try {
            localStorage.setItem(CLAVE_COLA, JSON.stringify(cola));
        } catch (e) {
            /* almacenamiento lleno o no disponible: no hay más remedio aquí */
        }
    }

    function generarClave() {
        if (window.crypto && crypto.randomUUID) return crypto.randomUUID();
        return 'env-' + Date.now() + '-' + Math.random().toString(16).slice(2);
    }

    function formularioAEntradas(form) {
        // Incluye también los campos asociados por el atributo form="..."
        // aunque estén fuera de la etiqueta <form> (ej. las cantidades
        // editables de Cierre de Entrega) — new FormData(form) ya los
        // toma en cuenta.
        return Array.from(new FormData(form).entries());
    }

    function enviarAlServidor(entradas, url, metodo) {
        var formData = new FormData();
        entradas.forEach(function (par) { formData.append(par[0], par[1]); });

        return fetch(url, {
            method: metodo || 'POST',
            body: formData,
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        });
    }

    function marcarSincronizado(etiqueta) {
        try {
            localStorage.setItem(CLAVE_SINCRONIZADO, etiqueta);
        } catch (e) { /* no crítico */ }
    }

    function actualizarBanner() {
        var banner = document.getElementById('banner-envios-pendientes');
        if (!banner) return;

        var sincronizado = null;
        try { sincronizado = localStorage.getItem(CLAVE_SINCRONIZADO); } catch (e) { /* no crítico */ }

        if (sincronizado) {
            banner.textContent = '✓ Ya se envió correctamente: ' + sincronizado + '.';
            banner.style.display = 'block';
            banner.style.background = '#d1fae5';
            banner.style.color = '#065f46';
            try { localStorage.removeItem(CLAVE_SINCRONIZADO); } catch (e) { /* no crítico */ }
            setTimeout(function () {
                if (leerCola().length === 0) banner.style.display = 'none';
            }, 8000);
            return;
        }

        var cola = leerCola();
        if (cola.length === 0) {
            banner.style.display = 'none';
            return;
        }

        banner.textContent = '📡 Sin señal: ' + cola.length + (cola.length === 1 ? ' envío guardado' : ' envíos guardados')
            + ', se ' + (cola.length === 1 ? 'enviará' : 'enviarán') + ' solo en cuanto regrese la conexión.';
        banner.style.display = 'block';
        banner.style.background = '#fef3c7';
        banner.style.color = '#92400e';
    }

    function intentarVaciarCola() {
        leerCola().forEach(function (item) {
            enviarAlServidor(item.entradas, item.url, item.metodo).then(function (resp) {
                if (!resp.ok) return; // sigue sin poder procesarse; se queda en la cola

                guardarCola(leerCola().filter(function (x) { return x.clave !== item.clave; }));
                marcarSincronizado(item.etiqueta);
                actualizarBanner();
            }).catch(function () {
                /* sigue sin señal: se reintenta en el próximo ciclo */
            });
        });
    }

    function proteger(formEl, opciones) {
        opciones = opciones || {};
        var etiqueta = opciones.etiqueta || 'tu envío';
        var antesDeEnviar = opciones.antesDeEnviar; // opcional: valida/rellena campos; regresa false para cancelar el envío

        formEl.addEventListener('submit', function (e) {
            e.preventDefault();

            if (typeof antesDeEnviar === 'function' && antesDeEnviar() === false) {
                return;
            }

            var clave = formEl.dataset.idempotencyKey || generarClave();
            formEl.dataset.idempotencyKey = clave;

            var entradas = formularioAEntradas(formEl);
            entradas.push(['idempotency_key', clave]);

            var botones = formEl.querySelectorAll('button[type="submit"]');
            botones.forEach(function (b) { b.disabled = true; });

            enviarAlServidor(entradas, formEl.action, formEl.method).then(function (resp) {
                if (resp.ok) {
                    window.location.href = resp.url;
                    return;
                }

                // Error real del servidor (no de señal: el fetch sí
                // resolvió) — ej. una validación que no se cachó en el
                // navegador. Se recarga para que el vendedor vea el
                // estado real de la pantalla.
                botones.forEach(function (b) { b.disabled = false; });
                alert('No se pudo completar el envío. La pantalla se va a recargar para revisar el motivo.');
                window.location.reload();
            }).catch(function () {
                // Sin señal: se guarda para reintentar solo, sin que el
                // vendedor tenga que quedarse en esta pantalla. No se usa
                // alert() aquí a propósito: es una ventana bloqueante que
                // interrumpe al vendedor justo cuando ya quiere avanzar al
                // siguiente punto — el banner persistente ya avisa lo mismo
                // sin bloquear nada.
                var cola = leerCola();
                if (! cola.some(function (x) { return x.clave === clave; })) {
                    cola.push({ clave: clave, url: formEl.action, metodo: formEl.method, entradas: entradas, etiqueta: etiqueta });
                    guardarCola(cola);
                }
                actualizarBanner();
                botones.forEach(function (b) { b.textContent = 'Guardado — se enviará solo'; });
            });
        });
    }

    return {
        proteger: proteger,
        intentarVaciarCola: intentarVaciarCola,
        actualizarBanner: actualizarBanner,
    };
})();
