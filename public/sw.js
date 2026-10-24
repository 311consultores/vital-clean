/* Service worker de Vital Clean: únicamente habilita que el navegador
 * ofrezca "Instalar la app" (Android/Chrome/Edge) — no cachea nada ni
 * guarda páginas para verlas sin conexión. El sistema siempre se pide a
 * la red (igual que hoy); el envío resiliente a conexión intermitente de
 * Nuevo Pedido/Cierre de Entrega ya se maneja aparte, en
 * public/js/envio-resiliente.js, sin depender de este Service Worker.
 */
self.addEventListener('install', () => self.skipWaiting());
self.addEventListener('activate', (event) => event.waitUntil(self.clients.claim()));

// Requisito para que el navegador considere el sitio "instalable": tener
// un Service Worker con un listener de fetch. No se llama a
// event.respondWith(), así que el navegador sigue pidiendo todo a la red
// exactamente igual que si este archivo no existiera.
self.addEventListener('fetch', () => {});
