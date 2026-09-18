# Changelog

## 1.38.1

- Register package migration paths during HTTP bootstrap as well as console bootstrap, allowing embedded Artisan migration calls to discover package migrations. No migrations execute during boot.
