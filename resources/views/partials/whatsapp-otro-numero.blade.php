{{--
    Enviar la misma nota por WhatsApp a un número capturado a mano, aunque
    no sea el teléfono registrado del cliente (ej. la persona de turno en
    recepción, o el cliente todavía no tiene teléfono en su catálogo).

    Requiere $mensaje (string, texto ya redactado del mensaje — ver
    App\Support\WhatsApp::mensajePara/mensajeRecoleccion/mensajeEntrega).
--}}
<details class="whatsapp-otro-numero" style="margin-top:.75rem;">
    <summary style="cursor:pointer; font-size:.85rem; color:#6b7280;">Enviar a otro número</summary>
    <div style="display:flex; gap:.5rem; margin-top:.5rem; flex-wrap:wrap;">
        <input type="tel" class="whatsapp-numero-input" placeholder="10 dígitos"
               style="padding:.5rem .6rem; border:1px solid #d1d5db; border-radius:.375rem; flex:1; min-width:180px;">
        <button type="button" class="btn btn-sm whatsapp-numero-enviar" style="background:#25D366;">Enviar</button>
    </div>
</details>
<script>
    (function () {
        var mensaje = {!! json_encode($mensaje) !!};

        document.querySelectorAll('.whatsapp-numero-enviar').forEach(function (boton) {
            if (boton.dataset.bound) return;
            boton.dataset.bound = '1';

            boton.addEventListener('click', function () {
                var input = boton.parentElement.querySelector('.whatsapp-numero-input');
                var digitos = (input.value || '').replace(/\D/g, '');

                if (digitos.length < 10) {
                    alert('Captura un número de teléfono válido (10 dígitos).');
                    return;
                }
                // Se asume siempre México: se toman los últimos 10 dígitos
                // y se antepone la lada 52 (funciona igual si ya la
                // escribieron de más).
                digitos = '52' + digitos.slice(-10);

                window.open('https://wa.me/' + digitos + '?text=' + encodeURIComponent(mensaje), '_blank', 'noopener');
            });
        });
    })();
</script>
