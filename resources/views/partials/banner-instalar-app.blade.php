{{--
    Banner para instalar Vital Clean como app (PWA) — clonado del patrón
    de Reportes 311 Consultores, adaptado a Blade/JS sin framework.
    Requiere public/js/instalar-app.js cargado e InstalarApp.iniciar()
    llamado en la página que incluya este parcial.
--}}
<div id="banner-instalar-app" style="display:none; position:fixed; left:0; right:0; bottom:0; z-index:60; background:#fff; border-top:1px solid #e5e7eb; box-shadow:0 -8px 30px rgba(0,0,0,.12); padding:.75rem 1rem; padding-bottom:max(.75rem, env(safe-area-inset-bottom)); align-items:center; gap:.75rem; font-size:.85rem; color:#1f2937;">
    <span style="font-size:1.3rem; flex-shrink:0;">📲</span>
    <div style="flex:1; min-width:0;">
        <strong>Instala Vital Clean en tu celular o tableta para entrar más rápido</strong>
        <div data-ios style="display:none; font-size:.75rem; color:#6b7280; margin-top:.2rem;">
            Toca el botón Compartir (el cuadro con la flecha ↑) y elige «Agregar a pantalla de inicio».
        </div>
        <div data-generico style="display:none; font-size:.75rem; color:#6b7280; margin-top:.2rem;">
            Abre el menú del navegador (⋮) y elige «Instalar aplicación» o «Agregar a pantalla de inicio».
        </div>
    </div>
    <button type="button" data-instalar style="display:none; flex-shrink:0; background:#26437D; color:#fff; border:none; padding:.5rem 1rem; border-radius:.375rem; font-weight:600; cursor:pointer;" onclick="InstalarApp.instalar()">
        Instalar
    </button>
    <button type="button" aria-label="Cerrar este mensaje" style="flex-shrink:0; background:transparent; border:none; font-size:1.3rem; line-height:1; color:#6b7280; cursor:pointer; padding:.25rem;" onclick="InstalarApp.descartar()">
        &times;
    </button>
</div>
<script src="{{ asset('js/instalar-app.js') }}"></script>
<script>InstalarApp.iniciar();</script>
