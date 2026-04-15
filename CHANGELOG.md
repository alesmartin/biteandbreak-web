# Bite&Break — Changelog & Development Specs

Registro de todos los cambios, features e integraciones del proyecto.

---

## Versión base

| Dato | Valor |
|------|-------|
| **Plataforma** | Magento Open Source |
| **Versión instalada** | 2.4.7-p1 |
| **Commit base** | `d10435b11ad` — "Magento Release 2.4.7-p1" |
| **Última versión disponible** | 2.4.8-p4 (actualización pendiente) |
| **PHP** | 8.2 |
| **MySQL** | 8.0 |
| **Search Engine** | Elasticsearch 7 |

---

## Entornos

| Entorno | URL | Descripción |
|---------|-----|-------------|
| **DEV** | http://localhost | Docker local |
| **PRO** | http://www.biteandbreak.com | Ionos VPS |
| **GitHub** | github.com/alesmartin/biteandbreak-web | Repositorio |
| **Docker Hub** | alesmartin/biteandbreak-dev:latest | Imagen DEV backup |

---

## [0.2.1] — 2026-04-15 — Docker Hub backup + restauración DEV

### Added
- Imagen DEV publicada en Docker Hub: `alesmartin/biteandbreak-dev:latest`
- Restauración de DEV desde backup de PRO (DB sincronizada)

### Specs
- Si se borra el entorno DEV, restaurar con: `docker pull alesmartin/biteandbreak-dev:latest`
- La DB de DEV se restaura con un dump de PRO y se actualiza `base_url` a `http://localhost/`

---

## [0.2.0] — 2026-04-09 — Tema luxury fashion

### Added
- Instalación de **Breeze Evolution** como tema base (Swissup, gratis)
- Creación de tema hijo **BiteBreak/luxury** basado en Breeze Evolution
- CSS personalizado estilo luxury fashion (fondo blanco, detalles dorados, footer negro)
- Tipografía en mayúsculas con letter-spacing para estética de moda
- Hover en productos con borde dorado
- Botones primarios negros con hover dorado

### Specs
- La tienda debe tener estética de moda de lujo
- Fondo blanco, texto negro, acentos dorados (#c9a96e)
- Footer oscuro (#111) con links en gris
- Botones con transición al hover
- Tipografía en uppercase con spacing generoso

---

## [0.1.2] — 2026-04-09 — Printify API

### Added
- Conexión API de Printify (token JWT configurado)
- Tienda Printify identificada: "My new store" (ID: 13169184)
- `printify.env.example` añadido al repositorio
- `printify.env` en `.gitignore` (credenciales protegidas)

### Specs
- Cuando el usuario tenga productos en Printify, sincronizar automáticamente a DEV y PRO
- El script de sync debe soportar tanto Printful como Printify
- Nunca commitear tokens ni contraseñas a GitHub

---

## [0.1.1] — 2026-04-08 — Printful sincronización

### Added
- Script `printful_sync.php` — sincroniza productos de Printful API a Magento
- Credenciales via `printful.env` (no commiteado)
- `printful.env.example` como plantilla
- Módulo `Printful/Integration` añadido al repositorio

### Products synced
- **Chanclas sublimadas** — SKU: 69D04307DE933 — Precio: 15.50€
- **Snapback Hat** — SKU: 69D042B36E531 — Precio: 16.50€

### Specs
- Productos sincronizados deben aparecer en categoría "Productos"
- Imágenes importadas desde CDN de Printful
- Stock ilimitado (print-on-demand, `manage_stock: false`)
- Visibilidad: Catalog + Search (visibility: 4)
- Asignados a website ID 1 (tienda principal)

---

## [0.1.0] — 2026-04-07 — Infraestructura base

### Added
- **VPS Ionos** configurado (Ubuntu 22.04, 1 vCore, 1GB RAM, 10GB NVMe)
- **Nginx** con soporte de archivos estáticos versionados de Magento
- **MySQL 8.0** optimizado para VPS de 1GB RAM
- **Elasticsearch 7** con heap limitado a 256MB
- **Swap** de 1GB para evitar OOM en servidor pequeño
- **Docker** local (DEV) con mismas versiones de PHP/MySQL que PRO
- **GitHub** repositorio configurado: `git@github.com:alesmartin/biteandbreak-web.git`
- SSH key `id_ed25519_github_alesmartin` para acceso al repo
- DNS `www.biteandbreak.com` → 82.165.10.29 configurado en Ionos
- Modo mantenimiento activado en PRO (tienda en desarrollo)

### Specs
- DEV y PRO deben tener siempre el mismo código base (via GitHub)
- `printful.env` y `printify.env` nunca se commitean (en .gitignore)
- Magento admin URL: `/admin_mdkshkq` (no estándar por seguridad)

### Fixed (durante setup)
- MySQL: contraseña sin `!` para compatibilidad con `mysql_native_password`
- Elasticsearch: OOM resuelto limitando heap a 256MB
- Nginx: archivos estáticos versionados con regex `^/static/(version\d*/)?`
- Admin login: `failures_num` reseteado en DB + `admin:user:unlock`
- Docker: conflicto puerto 80 con contenedor `php` antiguo (PHP 7.4, eliminado)

---

## Pendiente / Roadmap

### Próximas features
- [ ] SSL/HTTPS con Let's Encrypt para `www.biteandbreak.com`
- [ ] Script sync Printify → Magento (cuando haya productos)
- [ ] Fulfillment automático: pedidos Magento → Printful/Printify
- [ ] Actualización Magento 2.4.7-p1 → 2.4.8-p4
- [ ] Configuración de métodos de pago (Stripe / PayPal)
- [ ] Configuración de envíos
- [ ] Emails transaccionales personalizados
- [ ] Página "Sobre nosotros" y contenido CMS

### Decisiones de arquitectura
- **Print-on-demand**: Printful (activo) + Printify (en configuración)
- **Tema**: BiteBreak/luxury (hijo de Breeze Evolution)
- **Integración API**: scripts PHP propios en lugar de módulos OAuth (más estable)
- **Imágenes de producto**: importadas desde CDN de Printful/Printify

---

*Última actualización: 2026-04-09*
