@extends('layouts.app')

@section('title', 'Vital Clean — Nuevo Pedido')

@section('content')
    <div class="page-header"><h1>Nuevo Pedido</h1></div>

    @if ($errors->any())
        <div class="alert alert-error">
            @foreach ($errors->all() as $error)
                {{ $error }}<br>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('vendedor.recoleccion.store') }}" id="form-pedido">
        @csrf
        <div class="card" style="margin-bottom:1rem;">
            <div class="form-group">
                <label for="id_cliente">Cliente *</label>
                <select id="id_cliente" name="id_cliente" required style="max-width:100%;">
                    <option value="">— Buscar cliente —</option>
                    @forelse ($clientes as $cliente)
                        <option value="{{ $cliente->id_cliente }}" {{ old('id_cliente') == $cliente->id_cliente ? 'selected' : '' }}>
                            {{ $cliente->nombre_comercial }}
                        </option>
                    @empty
                        <option value="" disabled>No hay clientes con crédito activo</option>
                    @endforelse
                </select>
            </div>

            <div class="form-group">
                <label for="fecha_entrega_prog">Fecha de Entrega Comprometida</label>
                <input type="date" id="fecha_entrega_prog" name="fecha_entrega_prog"
                       value="{{ old('fecha_entrega_prog') }}" min="{{ now()->toDateString() }}" style="max-width:200px;">
            </div>
        </div>

        <div class="card" style="margin-bottom:1rem;">
            <h2 style="font-size:1.1rem; margin-top:0;">Buscar Prenda</h2>
            <p id="aviso-sin-cliente" style="color:#6b7280; font-size:.9rem;">Elige primero un cliente para ver las prendas con precio pactado.</p>

            <div id="buscador-prenda-wrap" style="position:relative; max-width:420px; display:none;">
                <input type="text" id="buscador-prenda" autocomplete="off"
                       placeholder="Escribe un nombre o categoría (ej. toalla, hotelería)..."
                       style="width:100%; padding:.65rem .8rem; font-size:1rem; border:1px solid #d1d5db; border-radius:.375rem;">
                <div id="sugerencias" style="display:none; position:absolute; z-index:10; left:0; right:0; top:100%;
                     background:#fff; border:1px solid #d1d5db; border-top:none; border-radius:0 0 .375rem .375rem;
                     max-height:280px; overflow-y:auto; box-shadow:0 4px 10px rgba(0,0,0,.08);"></div>
            </div>

            <div id="prenda-seleccionada" style="display:none; margin-top:1rem;">
                <strong id="prenda-nombre" style="display:block; margin-bottom:.6rem;"></strong>
                <div style="display:flex; flex-wrap:wrap; align-items:center; gap:.9rem;">
                    <input type="number" id="prenda-cantidad" min="1" step="1" value="1" style="width:75px;">
                    <span style="display:flex; gap:.9rem;">
                        <label style="display:flex; align-items:center; gap:.3rem; font-size:.85rem; font-weight:normal;">
                            <input type="radio" name="prenda-condicion" value="usada" checked style="width:auto;"> Usada
                        </label>
                        <label style="display:flex; align-items:center; gap:.3rem; font-size:.85rem; font-weight:normal;">
                            <input type="radio" name="prenda-condicion" value="nueva" style="width:auto;"> Nueva
                        </label>
                    </span>
                    <input type="text" id="prenda-color" placeholder="Color" list="colores-sugeridos"
                           style="display:none; width:130px;">
                    <label id="label-desmanche" style="display:flex; align-items:center; gap:.3rem; font-size:.85rem; font-weight:normal;">
                        <input type="checkbox" id="prenda-desmanche" style="width:auto;"> Desmanche
                    </label>
                    <button type="button" id="btn-agregar" class="btn btn-sm">Agregar</button>
                </div>
            </div>
            <datalist id="colores-sugeridos">
                <option value="Blanco">
                <option value="Color">
            </datalist>
        </div>

        <div class="card">
            <h2 style="font-size:1.1rem; margin-top:0;">Prendas del Pedido</h2>
            <div style="overflow-x:auto;">
                <table class="data-table" id="tabla-carrito">
                    <thead>
                        <tr><th>Prenda</th><th>Categoría</th><th style="width:110px;">Cantidad</th><th></th></tr>
                    </thead>
                    <tbody id="carrito-filas">
                        <tr id="carrito-vacio"><td colspan="4">Aún no has agregado ninguna prenda.</td></tr>
                    </tbody>
                </table>
            </div>
            <p style="margin-top:.75rem;"><strong>Total de piezas: <span id="total-piezas">0</span></strong></p>
        </div>

        <div id="inputs-cantidades"></div>

        <div class="form-actions">
            <button type="submit" class="btn">Continuar a Resumen</button>
            <a href="{{ route('vendedor.home') }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>

    @php
        $catalogoJs = $servicios->map(fn ($s) => [
            'id' => $s->id_servicio,
            'descripcion' => $s->descripcion,
            'categoria' => $s->categoria,
            'unidad' => $s->unidad,
            'requiere_color' => $s->requiere_color,
        ]);
    @endphp

    <script>
        // Bug: los precios se calculan por tarifa pactada por cliente
        // (RN-01), así que solo se puede levantar lo que el cliente elegido
        // tiene tarifado. El catálogo ya no se precarga completo: se pide
        // por AJAX cada vez que cambia el cliente.
        var CATALOGO = @json($catalogoJs);
        var CLIENTE_CATALOGO_ACTUAL = {{ old('id_cliente') ? (int) old('id_cliente') : 'null' }};
        var URL_SERVICIOS_CLIENTE = @json(route('vendedor.recoleccion.servicios', ['cliente' => '__ID__']));

        // Si la validación falló, no perder lo que ya se había armado.
        var CANTIDADES_PREVIAS = @json(old('cantidades', []));
        var CONDICION_PREVIA = @json(old('condicion', []));
        var COLOR_PREVIO = @json(old('color', []));
        var DESMANCHE_PREVIO = @json(old('desmanche', []));

        (function () {
            var selectCliente = document.getElementById('id_cliente');
            var avisoSinCliente = document.getElementById('aviso-sin-cliente');
            var buscadorWrap = document.getElementById('buscador-prenda-wrap');
            var input = document.getElementById('buscador-prenda');
            var caja = document.getElementById('sugerencias');
            var seleccionBox = document.getElementById('prenda-seleccionada');
            var nombreEl = document.getElementById('prenda-nombre');
            var cantidadEl = document.getElementById('prenda-cantidad');
            var colorEl = document.getElementById('prenda-color');
            var desmancheEl = document.getElementById('prenda-desmanche');
            var btnAgregar = document.getElementById('btn-agregar');
            var carritoFilas = document.getElementById('carrito-filas');
            var carritoVacio = document.getElementById('carrito-vacio');
            var totalPiezasEl = document.getElementById('total-piezas');
            var inputsCantidades = document.getElementById('inputs-cantidades');

            var carrito = {}; // { id_servicio: { descripcion, categoria, unidad, cantidad } }
            var prendaActual = null;

            function mostrarBuscador(mostrar) {
                avisoSinCliente.style.display = mostrar ? 'none' : 'block';
                buscadorWrap.style.display = mostrar ? 'block' : 'none';
            }

            function cargarServiciosDeCliente(idCliente, callback) {
                var url = URL_SERVICIOS_CLIENTE.replace('__ID__', idCliente);
                fetch(url, {headers: {'Accept': 'application/json'}})
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        CATALOGO = data;
                        CLIENTE_CATALOGO_ACTUAL = parseInt(idCliente, 10);
                        if (callback) callback();
                    })
                    .catch(function () {
                        CATALOGO = [];
                        alert('No se pudieron cargar las prendas tarifadas de este cliente.');
                    });
            }

            selectCliente.addEventListener('change', function () {
                var idCliente = selectCliente.value;
                if (!idCliente) {
                    mostrarBuscador(false);
                    return;
                }

                // Cambiar de cliente invalida el carrito armado (las prendas
                // y precios pactados dependen del cliente).
                if (CLIENTE_CATALOGO_ACTUAL !== null && CLIENTE_CATALOGO_ACTUAL !== parseInt(idCliente, 10) && Object.keys(carrito).length > 0) {
                    carrito = {};
                }

                cargarServiciosDeCliente(idCliente, function () {
                    renderCarrito();
                    mostrarBuscador(true);
                });
            });

            if (CLIENTE_CATALOGO_ACTUAL) {
                mostrarBuscador(true);
            }

            function normalizar(texto) {
                return (texto || '').toString().toLowerCase()
                    .normalize('NFD').replace(/[\u0300-\u036f]/g, '');
            }

            function buscar(termino) {
                // Solo coincide contra el nombre de la prenda, no la
                // categoría — antes "hotelería" mostraba todas las prendas
                // de esa categoría en vez de acotar a lo que se escribió.
                var q = normalizar(termino);
                if (q.length === 0) return [];
                return CATALOGO.filter(function (p) {
                    return normalizar(p.descripcion).includes(q);
                }).slice(0, 10);
            }

            function mostrarSugerencias(resultados) {
                if (resultados.length === 0) {
                    caja.innerHTML = '<div style="padding:.6rem .8rem; color:#6b7280; font-size:.85rem;">Sin resultados.</div>';
                    caja.style.display = 'block';
                    return;
                }
                caja.innerHTML = resultados.map(function (p) {
                    return '<div class="sugerencia" data-id="' + p.id + '" ' +
                        'style="padding:.6rem .8rem; cursor:pointer; border-bottom:1px solid #f3f4f6; font-size:.9rem;">' +
                        '<strong>' + p.descripcion + '</strong>' +
                        '<span style="color:#6b7280;"> — ' + (p.categoria || 'Sin categoría') + ' (' + p.unidad + ')</span>' +
                        '</div>';
                }).join('');
                caja.style.display = 'block';

                caja.querySelectorAll('.sugerencia').forEach(function (el) {
                    el.addEventListener('mouseenter', function () { el.style.background = '#F5F5F5'; });
                    el.addEventListener('mouseleave', function () { el.style.background = ''; });
                    el.addEventListener('click', function () {
                        var id = parseInt(el.getAttribute('data-id'), 10);
                        seleccionarPrenda(CATALOGO.find(function (p) { return p.id === id; }));
                    });
                });
            }

            function seleccionarPrenda(prenda) {
                prendaActual = prenda;
                nombreEl.textContent = prenda.descripcion + ' (' + prenda.unidad + ')';
                cantidadEl.value = 1;
                document.querySelector('input[name="prenda-condicion"][value="usada"]').checked = true;
                colorEl.value = '';
                colorEl.style.display = prenda.requiere_color ? 'inline-block' : 'none';
                desmancheEl.checked = false;
                seleccionBox.style.display = 'block';
                caja.style.display = 'none';
                input.value = prenda.descripcion;
            }

            input.addEventListener('input', function () {
                mostrarSugerencias(buscar(input.value));
            });
            input.addEventListener('focus', function () {
                if (input.value.trim().length > 0) mostrarSugerencias(buscar(input.value));
            });
            document.addEventListener('click', function (e) {
                if (!caja.contains(e.target) && e.target !== input) caja.style.display = 'none';
            });

            btnAgregar.addEventListener('click', function () {
                if (!prendaActual) return;
                var cantidad = Math.max(1, parseInt(cantidadEl.value, 10) || 1);
                var existente = carrito[prendaActual.id];
                var condicion = document.querySelector('input[name="prenda-condicion"]:checked').value;
                carrito[prendaActual.id] = {
                    descripcion: prendaActual.descripcion,
                    categoria: prendaActual.categoria,
                    unidad: prendaActual.unidad,
                    cantidad: existente ? existente.cantidad + cantidad : cantidad,
                    // Modificadores por prenda (#5/#11/#12): si esta prenda ya
                    // estaba en el carrito, se actualizan con lo último
                    // capturado — un mismo tipo de prenda no se puede partir
                    // en nueva/usada como líneas separadas.
                    condicion: condicion,
                    color: prendaActual.requiere_color ? colorEl.value.trim() : '',
                    desmanche: desmancheEl.checked,
                };
                renderCarrito();

                // listo para buscar la siguiente prenda
                prendaActual = null;
                seleccionBox.style.display = 'none';
                input.value = '';
                input.focus();
            });

            function quitarDelCarrito(id) {
                delete carrito[id];
                renderCarrito();
            }

            function cambiarCantidad(id, valor) {
                var cantidad = Math.max(1, parseInt(valor, 10) || 1);
                if (carrito[id]) {
                    carrito[id].cantidad = cantidad;
                    renderCarrito();
                }
            }

            function etiquetasModificadores(item) {
                var etiquetas = '';
                if (item.condicion === 'nueva') {
                    etiquetas += ' <span class="badge" style="background:var(--azul-claro);">Nueva</span>';
                }
                if (item.color) {
                    etiquetas += ' <span class="badge" style="background:#6b7280;">' + item.color + '</span>';
                }
                if (item.desmanche) {
                    etiquetas += ' <span class="badge" style="background:var(--amarillo);">Desmanche</span>';
                }
                return etiquetas ? '<br>' + etiquetas : '';
            }

            function renderCarrito() {
                var ids = Object.keys(carrito);
                var total = 0;

                if (ids.length === 0) {
                    carritoFilas.innerHTML = '';
                    carritoFilas.appendChild(carritoVacio);
                } else {
                    carritoFilas.innerHTML = ids.map(function (id) {
                        var item = carrito[id];
                        total += item.cantidad;
                        return '<tr>' +
                            '<td>' + item.descripcion + etiquetasModificadores(item) + '</td>' +
                            '<td>' + (item.categoria || '—') + '</td>' +
                            '<td><input type="number" min="1" step="1" value="' + item.cantidad + '" ' +
                                'style="width:70px;" data-cambiar-cantidad="' + id + '"></td>' +
                            '<td><button type="button" class="btn btn-sm btn-danger" data-quitar="' + id + '">Quitar</button></td>' +
                            '</tr>';
                    }).join('');
                }

                totalPiezasEl.textContent = total;

                carritoFilas.querySelectorAll('[data-quitar]').forEach(function (btn) {
                    btn.addEventListener('click', function () { quitarDelCarrito(btn.getAttribute('data-quitar')); });
                });
                carritoFilas.querySelectorAll('[data-cambiar-cantidad]').forEach(function (inp) {
                    inp.addEventListener('change', function () {
                        cambiarCantidad(inp.getAttribute('data-cambiar-cantidad'), inp.value);
                    });
                });

                // hidden inputs para enviar con el form: cantidades[id]=cantidad,
                // más un modificador por prenda (condicion/color/desmanche).
                inputsCantidades.innerHTML = ids.map(function (id) {
                    var item = carrito[id];
                    var html = '<input type="hidden" name="cantidades[' + id + ']" value="' + item.cantidad + '">';
                    html += '<input type="hidden" name="condicion[' + id + ']" value="' + (item.condicion || 'usada') + '">';
                    if (item.color) {
                        html += '<input type="hidden" name="color[' + id + ']" value="' + item.color.replace(/"/g, '&quot;') + '">';
                    }
                    if (item.desmanche) {
                        html += '<input type="hidden" name="desmanche[' + id + ']" value="1">';
                    }
                    return html;
                }).join('');
            }

            document.getElementById('form-pedido').addEventListener('submit', function (e) {
                if (Object.keys(carrito).length === 0) {
                    e.preventDefault();
                    alert('Agrega al menos una prenda al pedido.');
                }
            });

            Object.keys(CANTIDADES_PREVIAS).forEach(function (id) {
                var cantidad = parseInt(CANTIDADES_PREVIAS[id], 10);
                var prenda = CATALOGO.find(function (p) { return p.id === parseInt(id, 10); });
                if (prenda && cantidad > 0) {
                    carrito[id] = {
                        descripcion: prenda.descripcion,
                        categoria: prenda.categoria,
                        unidad: prenda.unidad,
                        cantidad: cantidad,
                        condicion: CONDICION_PREVIA[id] || 'usada',
                        color: COLOR_PREVIO[id] || '',
                        desmanche: !!DESMANCHE_PREVIO[id],
                    };
                }
            });
            renderCarrito();
        })();
    </script>
@endsection
