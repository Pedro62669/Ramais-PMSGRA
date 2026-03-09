const CACHE_NAME = "ramais-cache-v3";
const ASSETS = [
  "./",
  "./index.php",
  "./manifest.json",
  "./ico.png",
  "./icon-192.png",
  "./icon-512.png"
];

self.addEventListener("install", (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => cache.addAll(ASSETS))
  );
});

self.addEventListener("activate", (event) => {
  event.waitUntil(
    caches.keys().then((keys) =>
      Promise.all(keys.filter((k) => k !== CACHE_NAME).map((k) => caches.delete(k)))
    )
  );
});

self.addEventListener("fetch", (event) => {
  const request = event.request;
  if (request.method !== "GET") return; // nunca intercepta POST/PUT/DELETE

  const url = new URL(request.url);

  // Tratar documentos HTML (navegação) com network-first para evitar cache de páginas dinâmicas
  const isHTMLRequest = request.mode === "navigate" || (request.headers.get("accept") || "").includes("text/html");

  if (isHTMLRequest) {
    event.respondWith(
      fetch(request)
        .then((response) => {
          // não cacheamos páginas HTML dinâmicas
          return response;
        })
        .catch(() => caches.match("./index.php"))
    );
    return;
  }

  // Para mesmos origem e assets estáticos
  const staticDestinations = ["script", "style", "image", "font", "manifest"];
  if (url.origin === self.location.origin && staticDestinations.includes(request.destination)) {
    // Scripts/Styles: stale-while-revalidate para evitar CSS/JS preso no cache antigo
    if (request.destination === "script" || request.destination === "style") {
      event.respondWith(
        caches.match(request).then((cached) => {
          const fetchPromise = fetch(request)
            .then((response) => {
              if (response && response.ok) {
                const responseClone = response.clone();
                caches.open(CACHE_NAME).then((cache) => cache.put(request, responseClone));
              }
              return response;
            })
            .catch(() => cached);

          return cached || fetchPromise;
        })
      );
      return;
    }

    // Outros assets estáticos: cache-first
    event.respondWith(
      caches.match(request).then((cached) =>
        cached || fetch(request).then((response) => {
          const responseClone = response.clone();
          caches.open(CACHE_NAME).then((cache) => cache.put(request, responseClone));
          return response;
        })
      )
    );
    return;
  }

  // Outros GETs: network-first com fallback ao cache
  event.respondWith(
    fetch(request).catch(() => caches.match(request))
  );
});
