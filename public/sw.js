/**
 * Service worker — GesPerES Espace agent (PWA).
 *
 * Stratégie respectueuse de la confidentialité :
 *  - les pages HTML authentifiées ne sont JAMAIS mises en cache (réseau d'abord,
 *    repli sur une page hors-ligne neutre) ;
 *  - seuls les assets statiques (build Vite, images, icônes) sont mis en cache
 *    (cache d'abord) pour un démarrage instantané et une coquille hors-ligne.
 */
const VERSION = 'gesperes-agent-v1';
const SHELL = `${VERSION}-shell`;
const ASSETS = `${VERSION}-assets`;
const OFFLINE_URL = '/offline.html';

const PRECACHE = [
    OFFLINE_URL,
    '/manifest.webmanifest',
    '/images/icons/icon-192.png',
    '/images/icons/icon-512.png',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(SHELL).then((cache) => cache.addAll(PRECACHE)).then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((cles) => Promise.all(
                cles.filter((c) => !c.startsWith(VERSION)).map((c) => caches.delete(c))
            ))
            .then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const { request } = event;

    if (request.method !== 'GET' || new URL(request.url).origin !== self.location.origin) {
        return;
    }

    // Navigations (pages) : réseau d'abord, repli hors-ligne. Aucune mise en cache.
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request).catch(() => caches.match(OFFLINE_URL))
        );
        return;
    }

    // Assets statiques : cache d'abord, puis réseau (et on alimente le cache).
    const url = new URL(request.url);
    const estStatique = /\/(build|images)\//.test(url.pathname)
        || /\.(css|js|png|jpe?g|svg|webp|woff2?)$/.test(url.pathname);

    if (estStatique) {
        event.respondWith(
            caches.match(request).then((cachee) => cachee || fetch(request).then((reponse) => {
                const copie = reponse.clone();
                caches.open(ASSETS).then((cache) => cache.put(request, copie));
                return reponse;
            }).catch(() => cachee))
        );
    }
});
