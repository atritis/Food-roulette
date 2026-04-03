# Food Roulette

Rezeptverwaltung als Progressive Web App.

## Tech Stack

- **Frontend:** Vue 3 + Vite + Pinia + vue-router
- **Backend:** PHP 8.4 + Slim Framework 4
- **Datenbank:** MySQL 8
- **Styling:** SCSS mit Dark Mode

## Setup

### Voraussetzungen

- Node.js 18+
- PHP 8.1+
- Composer
- Docker & Docker Compose (für MySQL)

### 1. Datenbank starten

```bash
docker-compose up -d
```

### 2. Migrationen ausführen

```bash
php api/migrate.php
```

### 3. Backend starten

```bash
cd api && php -S localhost:8080 -t public
```

### 4. Frontend starten

```bash
cd frontend && npm install && npm run dev
```

Die App ist dann unter `http://localhost:5173` erreichbar.

## API

Die API läuft unter `/api`. Der `POST /api/recipes`-Endpunkt unterstützt Bearer-Token-Authentifizierung für externen Zugriff:

```bash
curl -X POST http://localhost:8080/api/recipes \
  -H "Authorization: Bearer <token>" \
  -H "Content-Type: application/json" \
  -d '{"title": "Beispielrezept", "instructions": "...", "servings": 4}'
```

Den Token konfigurierst du in `api/config/settings.php`.

## Produktion

```bash
cd frontend && npm run build
```

Das Build-Ergebnis liegt in `frontend/dist/`.
