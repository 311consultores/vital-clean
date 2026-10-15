{{--
    Refresca en silencio, cada cierto tiempo, el contenido de un contenedor
    con id="tabla-auto-actualizable" en esta misma página: vuelve a pedir
    la URL actual (conservando búsqueda/página/pestaña) y reemplaza solo
    ese bloque, sin recargar la pantalla completa ni perder lo que el
    usuario esté escribiendo en el buscador de arriba.

    No es un socket de verdad (este hosting compartido no puede correr un
    proceso de Node permanente): es "polling" simple por fetch().

    Parámetro opcional: $intervaloMs (default 20000).
--}}
@php $intervaloMs ??= 20000; @endphp
<script>
    (function () {
        var contenedor = document.getElementById('tabla-auto-actualizable');
        if (!contenedor) return;

        function actualizar() {
            // No gasta peticiones en pestañas que no se están viendo.
            if (document.visibilityState === 'hidden') return;

            var url = new URL(window.location.href);
            url.searchParams.set('_poll', '1');

            fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function (r) { return r.text(); })
                .then(function (html) {
                    var doc = new DOMParser().parseFromString(html, 'text/html');
                    var nuevo = doc.getElementById('tabla-auto-actualizable');
                    if (nuevo) contenedor.innerHTML = nuevo.innerHTML;
                })
                .catch(function () { /* silencioso: se reintenta en el siguiente ciclo */ });
        }

        setInterval(actualizar, {{ $intervaloMs }});
    })();
</script>
