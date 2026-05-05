# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

### Frontend (Yarn)
```bash
yarn dev      # Vite dev server on port 5173
yarn build    # Production build + copies PWA manifest files
yarn watch    # Build in watch mode
```

### Backend (PHP / SilverStripe)
```bash
composer install
ddev sake db:build    # Run database migrations

composer lint         # PHPCS linting
composer fix          # Auto-fix code style
composer rector       # Run Rector refactoring
composer phpstan      # Static analysis
```

## Architecture

Photobox is a photo booth PWA. The frontend is a **Vue 3 SPA** (Vite + Pinia + Vue Router) served by a **SilverStripe 6 CMS** backend (PHP 8.3+). Local dev runs via **ddev** with MySQL.

### Frontend (`app/client/src/`)
- Entry point: `js/main.js` mounts `vue/App.vue`
- State: single Pinia store in `vue/store.js` (`usePhotoboxStore`) — holds selected event, camera stream, captured photo, filters, persons
- Routing in `vue/router.js`:
  - `/` → `EventSetup.vue` — pick event and filter set
  - `/capture` → `PhotoCapture.vue` — camera + photo taking
  - `/person-selection` → `PersonSelection.vue` — face-api.js recognition
  - `/success` → `Success.vue` — QR code download
- Styles: SCSS in `scss/`, with per-view files under `scss/views/`
- Build output goes to `app/client/dist/`

### Backend (`app/src/`)
- **`Controllers/APIController.php`** — all REST endpoints under `/api/`:
  - `GET /api/events`, `/api/events/{id}/filtersets`, `/api/events/{id}/persons`
  - `POST /api/photos` — saves captured photo
  - Filter set management endpoints
- **`Controllers/PhotoDownloadController.php`** — serves photo downloads
- **Models**: `Event`, `Photo`, `Person`, `Filter`, `FilterSet`, `Entry` — SilverStripe ORM
  - `Event` has many `Photo` and `Person`; many-many with `FilterSet`
  - `Person` stores profile image used for face recognition matching
- **`Admin/`**: SilverStripe admin panels for events and filters
- Config: `app/_config/` (YAML)

### Key integrations
- **face-api.js** — client-side face detection and matching in `PersonSelection.vue`
- **vite-plugin-pwa** (Workbox) — service worker with auto-update; `copy-pwa` script copies manifest files post-build
- **atwx/silverstripe-vitehelper** — injects Vite-built assets into SilverStripe templates
- **QR code** — generated client-side after photo save, links to download URL
