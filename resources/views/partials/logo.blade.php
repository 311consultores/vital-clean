{{--
    Identidad de marca — Lavandería Vital Clean.

    Recreación vectorial (SVG) del logo del cliente: gota azul marino con
    burbujas blancas + wordmark "LAVANDERÍA VITAL" / "Clean". Se usa en vez
    de un archivo de imagen para que se vea nítido a cualquier tamaño y no
    dependa de subir un asset binario al hosting.

    Parámetros opcionales (pásalos con @include('partials.logo', [...])):
      - $size    (int, px del ícono, default 56)
      - $stacked (bool, ícono arriba y texto abajo centrado; default true)
      - $light   (bool, versión clara para fondos oscuros; default false)
--}}
@php
    $size ??= 56;
    $stacked ??= true;
    $light ??= false;

    $navy = $light ? '#FFFFFF' : '#1B1A4B';
    $red = $light ? '#FFD166' : '#E2384E';
@endphp
<span class="vc-logo {{ $stacked ? 'vc-logo--stacked' : 'vc-logo--inline' }}">
    <svg class="vc-logo-icon" width="{{ $size }}" height="{{ $size }}" viewBox="0 0 24 24" aria-hidden="true">
        <path fill="{{ $navy }}" d="M12 2C12 2 5 9.6 5 14.6C5 18.7 8.13 22 12 22C15.87 22 19 18.7 19 14.6C19 9.6 12 2 12 2Z"/>
        <circle cx="9.3" cy="11.6" r="1.7" fill="{{ $light ? '#1B1A4B' : '#FFFFFF' }}"/>
        <circle cx="13.4" cy="9.9" r="1.05" fill="{{ $light ? '#1B1A4B' : '#FFFFFF' }}"/>
        <circle cx="14.6" cy="13" r="1.35" fill="{{ $light ? '#1B1A4B' : '#FFFFFF' }}"/>
        <circle cx="10.6" cy="15.5" r="2.15" fill="{{ $light ? '#1B1A4B' : '#FFFFFF' }}"/>
    </svg>
    <span class="vc-logo-text">
        <span class="vc-logo-super" style="color: {{ $navy }};">Lavandería</span>
        <span class="vc-logo-main" style="color: {{ $navy }};">Vital</span>
        <span class="vc-logo-script" style="color: {{ $red }};">Clean</span>
    </span>
</span>
