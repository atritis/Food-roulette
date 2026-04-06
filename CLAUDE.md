# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Development Commands

### Start everything

```bash
# 1. MySQL (Docker)
docker-compose up -d

# 2. Backend (PHP dev server)
cd api && php -S localhost:8080 -t public

# 3. Frontend (Vite dev server)
cd frontend && npm run dev
# → http://localhost:5173/food-roulette/
```

### Database migrations

```bash
php api/migrate.php
```

Migrations are sequential `.sql` files in `api/migrations/`. The runner tracks applied files in a `_migrations` table and is idempotent — safe to re-run.

### Frontend build

```bash
cd frontend && npm run build
# Output: frontend/dist/
```

## Architecture

### Overview

Two independent services: a PHP/Slim REST API and a Vue 3 SPA. They share no code. The frontend proxies `/api` to the backend during development (Vite proxy config). In production the frontend is a static build served separately; the PHP API runs behind a web server with `APP_BASE_PATH` set.

### Backend (`api/`)

- **Entry point:** `api/public/index.php` — bootstraps Slim 4 via PHP-DI bridge, registers middleware and routes
- **Routes:** defined in `api/config/routes.php`
- **Config:** `api/config/settings.php` — reads DB credentials and bearer token from env vars, falls back to defaults
- **Models** (`api/src/Models/`): static-method PDO classes — `Recipe`, `Tag`, `Ingredient`, `RecipeImage`. All relations are loaded eagerly via `Recipe::loadRelations()`.
- **Auth:** `BearerAuthMiddleware` is applied only to `POST /api/recipes`. It skips auth entirely when no `Authorization` header is present (SPA usage), and rejects invalid tokens when the header is present.
- **Images:** uploaded files stored in `api/uploads/{recipe_id}/`, served via a dedicated Slim route at `/api/uploads/{path}`.
- **No ORM** — raw PDO throughout.

### Frontend (`frontend/src/`)

- **State:** single Pinia store (`stores/recipeStore.js`) holds `randomRecipes`, `searchResults`, `currentRecipe`, and loading flags.
- **API access:** all HTTP calls go through `composables/useApi.js`, which builds the base URL from `import.meta.env.BASE_URL` (or `VITE_API_BASE_URL` env override).
- **Views:** `HomeView` (random suggestions + search) and `RecipeDetailView` (display + inline edit).
- **Inline editing:** `RecipeDetailView` deep-copies `currentRecipe` into `editData` on edit start; saves by calling `store.updateRecipe()` then re-fetching.
- **Styling:** SCSS with `@use 'variables'` / `@use 'mixins'`. All colors are CSS custom properties defined in `_variables.scss`. Fixed single theme — no dark mode.
- **PWA:** Vite PWA plugin (`vite-plugin-pwa`). Icons live in `frontend/public/icons/`. The app is deployed at base path `/food-roulette/`, so manifest icon `src` values must be **relative** (no leading `/`) to resolve correctly.

### Key design decisions

- `POST /api/recipes` bearer token auth is intentionally optional (no header = pass through). This allows both SPA use without credentials and external import scripts with a token.
- Search results are ordered by priority: title matches → tag matches → ingredient matches, with exact matches before partial within each group.
- The `/import-recipe` Claude Code skill (`~/.claude/skills/import-recipe.md`) fetches a recipe URL, extracts structured data, and POSTs to the API using the bearer token.
- Image uploads resize to max 1200px width (server-side, configured in `settings.php`).
