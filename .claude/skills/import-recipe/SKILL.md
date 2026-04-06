---
description: Fetch a recipe from a URL and import it into the Food Roulette API, updating existing entries by title
argument-hint: <recipe-url>
---

Import the recipe at this URL into the Food Roulette application: $ARGUMENTS

Follow these steps exactly:

## Configuration

The default values below target the production deployment. To use a local dev server instead, set:
- `API_BASE=http://localhost:8080/api`
- `API_TOKEN=recipe-adder-token-xXx`

## Step 1 — Fetch the page

Use the Bash tool to download the page to a temp file:

```bash
curl -s -A "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36" "$URL" > /tmp/recipe_import.html
```

## Step 2 — Extract recipe data

Run this Python snippet via Bash to extract structured data from the HTML:

```bash
python3 << 'PYEOF'
import re, json

with open('/tmp/recipe_import.html', 'r', encoding='utf-8', errors='replace') as f:
    html = f.read()

pattern = r'<script[^>]*type="application/ld\+json"[^>]*>(.*?)</script>'
for m in re.findall(pattern, html, re.DOTALL):
    try:
        data = json.loads(m.strip())
        items = data if isinstance(data, list) else [data]
        for item in items:
            if item.get('@type') == 'Recipe':
                print(json.dumps(item, ensure_ascii=False, indent=2))
    except:
        pass
PYEOF
```

From the output extract:

- **title**: The recipe name (original language)
- **instructions**: Full cooking instructions as plain text, newline-separated steps
- **prep_time**: Preparation time in minutes (integer). ISO 8601 PT format: `PT0H20M` → 20. Use 0 if not found.
- **cook_time**: Cook/bake time in minutes (totalTime minus prepTime). Use 0 if not found.
- **servings**: Number of portions (integer). Default to 4 if not found.
- **ingredients**: From `recipeIngredient` array, each parsed into:
  - `name` (string): ingredient name in original language
  - `quantity` (number or null): numeric amount. Null if none.
  - `unit` (string or null): unit of measure (e.g. "g", "ml", "EL", "TL"). Null if none.
- **tags**: 1–4 relevant tags based on `keywords`/`recipeCategory`, each with:
  - `name` (string): e.g. "Frühstück", "Kuchen", "Vegetarisch", "Hauptgericht", "Dessert", "Suppe", "Pasta"
  - `color` (string): hex color fitting the category

Also extract **image URLs**: collect all unique image URLs from:
- The `image` field in the JSON-LD Recipe object
- `<meta property="og:image">` tags
- Any `<img>` tags with `src` or `data-src` matching the CDN domain (e.g. `img.chefkoch-cdn.de`) that appear to be recipe photos (not icons/avatars)

Deduplicate and keep at most 5 image URLs.

## Step 3 — Check for existing recipe

Search for an existing recipe with the same title:

```bash
API_BASE="${API_BASE:-https://wu.zone/food-roulette/api}"
curl -s "$API_BASE/recipes/search?q=TITLE_HERE&in_title=1&in_ingredients=0&in_tags=0"
```

- If the response contains a result whose `title` exactly matches (case-insensitive), **use that recipe's `id`** and proceed with PUT (update) instead of POST (create).
- Otherwise, create a new recipe with POST.

## Step 4 — Create or update the recipe

Always write the payload to a temp file first to avoid shell quoting issues with Unicode characters:

```bash
cat > /tmp/recipe_payload.json << 'EOF'
{
  "title": "...",
  ...
}
EOF
```

**If creating (no existing match):**

```bash
API_BASE="${API_BASE:-https://wu.zone/food-roulette/api}"
API_TOKEN="${API_TOKEN:-recipe-adder-token-xXx}"
curl -s -X POST "$API_BASE/recipes" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $API_TOKEN" \
  -d @/tmp/recipe_payload.json
```

Note the `id` from the response.

**If updating (existing recipe found):**

```bash
API_BASE="${API_BASE:-https://wu.zone/food-roulette/api}"
API_TOKEN="${API_TOKEN:-recipe-adder-token-xXx}"
curl -s -X PUT "$API_BASE/recipes/ID" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $API_TOKEN" \
  -d @/tmp/recipe_payload.json
```

JSON payload shape:
```json
{
  "title": "Der perfekte Pfannkuchen",
  "instructions": "Alle Zutaten verrühren...",
  "prep_time": 10,
  "cook_time": 20,
  "servings": 4,
  "tags": [
    { "name": "Frühstück", "color": "#f59e0b" }
  ],
  "ingredients": [
    { "name": "Weizenmehl", "quantity": 200, "unit": "g" },
    { "name": "Eier", "quantity": 3, "unit": null }
  ]
}
```

## Step 5 — Upload images

For each image URL collected in Step 2:

1. Download the image to a temp file:
```bash
curl -sL -A "Mozilla/5.0" "IMAGE_URL" -o /tmp/recipe_img_N.jpg
```

2. Upload it to the recipe (use the `id` from Step 4):
```bash
API_BASE="${API_BASE:-https://wu.zone/food-roulette/api}"
API_TOKEN="${API_TOKEN:-recipe-adder-token-xXx}"
curl -s -X POST "$API_BASE/recipes/ID/images" \
  -H "Authorization: Bearer $API_TOKEN" \
  -F "images[]=@/tmp/recipe_img_N.jpg"
```

Skip images that fail to download (empty file or curl error). When updating an existing recipe, only upload images if the recipe currently has no images (`"images": []` in the response).

## Step 6 — Report the result

Show the user:
- Whether the recipe was **created** or **updated**
- The recipe title and its `id`
- Number of images uploaded
- A short summary of ingredients and tags
- Any error messages if steps failed
