{{--
    #9: en Planta/Producción, subir la foto de una incidencia fallaba
    cuando la foto venía directo de la cámara del celular — los equipos
    modernos capturan JPEG de 8-15MB, y el hosting compartido suele tener
    límites de PHP (upload_max_filesize/post_max_size) más bajos que eso,
    así que el archivo se perdía antes de que Laravel pudiera validarlo.

    Este script comprime la foto en el navegador (redimensiona a máx.
    1600px de lado mayor, JPEG calidad 70%) apenas se selecciona, antes de
    que el formulario se envíe. Si la compresión falla por cualquier razón
    (formato no soportado por el navegador, etc.), se sube el archivo
    original tal cual — nunca bloquea el envío.
--}}
<script>
    (function () {
        function comprimirImagen(archivo, dimensionMaxima, calidad) {
            return new Promise(function (resolve, reject) {
                var url = URL.createObjectURL(archivo);
                var img = new Image();
                img.onload = function () {
                    URL.revokeObjectURL(url);
                    var ancho = img.width;
                    var alto = img.height;
                    if (ancho > dimensionMaxima || alto > dimensionMaxima) {
                        var escala = dimensionMaxima / Math.max(ancho, alto);
                        ancho = Math.round(ancho * escala);
                        alto = Math.round(alto * escala);
                    }
                    var canvas = document.createElement('canvas');
                    canvas.width = ancho;
                    canvas.height = alto;
                    canvas.getContext('2d').drawImage(img, 0, 0, ancho, alto);
                    canvas.toBlob(function (blob) {
                        if (!blob) {
                            reject(new Error('No se pudo generar la imagen comprimida.'));
                            return;
                        }
                        var nombre = archivo.name.replace(/\.[^.]+$/, '') + '.jpg';
                        resolve(new File([blob], nombre, {type: 'image/jpeg'}));
                    }, 'image/jpeg', calidad);
                };
                img.onerror = function () {
                    URL.revokeObjectURL(url);
                    reject(new Error('El navegador no pudo leer la imagen.'));
                };
                img.src = url;
            });
        }

        document.querySelectorAll('input.foto-incidencia').forEach(function (input) {
            input.addEventListener('change', function () {
                var archivo = input.files[0];
                if (!archivo || archivo.type.indexOf('image/') !== 0) {
                    return;
                }
                comprimirImagen(archivo, 1600, 0.7).then(function (comprimido) {
                    var listaArchivos = new DataTransfer();
                    listaArchivos.items.add(comprimido);
                    input.files = listaArchivos.files;
                }).catch(function () {
                    // Si falla la compresión se sube el archivo tal cual venía.
                });
            });
        });
    })();
</script>
