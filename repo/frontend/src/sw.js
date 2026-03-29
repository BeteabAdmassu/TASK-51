/// <reference lib="webworker" />
import { cleanupOutdatedCaches, createHandlerBoundToURL, precacheAndRoute } from 'workbox-precaching'
import { registerRoute } from 'workbox-routing'
import { StaleWhileRevalidate } from 'workbox-strategies'

const AUTH_CACHE_NAMES = ['roadlink-rides-cache', 'roadlink-chat-cache']

const purgeAuthCaches = async () => {
  const cacheNames = await caches.keys()

  await Promise.all(
    cacheNames
      .filter((name) => AUTH_CACHE_NAMES.some((prefix) => name.startsWith(prefix)))
      .map((name) => caches.delete(name)),
  )
}

precacheAndRoute(self.__WB_MANIFEST)
cleanupOutdatedCaches()

registerRoute(
  ({ request }) => request.mode === 'navigate',
  createHandlerBoundToURL('/index.html'),
)

registerRoute(
  ({ request }) => request.destination === 'script' || request.destination === 'style' || request.destination === 'font',
  new StaleWhileRevalidate({ cacheName: 'roadlink-shell-assets' }),
)

self.addEventListener('message', (event) => {
  if (event.data?.type === 'ROADLINK_PURGE_AUTH_CACHES') {
    event.waitUntil(purgeAuthCaches())
  }
})
