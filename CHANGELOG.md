# Changelog

## 1.39.0

- Add opt-in operation-to-class mappings for safe committed CRUD outcomes, including relation-aware no-op detection. Existing synchronous events retain their signatures and timing.


## 1.38.1

- Register package migration paths during HTTP bootstrap as well as console bootstrap, allowing embedded Artisan migration calls to discover package migrations. No migrations execute during boot.
