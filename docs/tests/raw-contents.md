# Raw Contents API — Tests

**Variables**
```
TOKEN="ton_token"
BLUEPRINT_ID=1
```

---

## 1. Création (valide → 202)

```
POST http://localhost:8000/api/raw-contents
```

**Headers**
```
Accept: application/json
Content-Type: application/json
Authorization: Bearer {{TOKEN}}
```

**Body**
```json
{
  "title": "Mon article sur Docker",
  "content": "Voici mes notes brutes sur Docker Compose et microservices Laravel...",
  "source_type": "raw",
  "blueprint_id": 1
}
```

**Curl avec timing**
```bash
curl -s -X POST http://localhost:8000/api/raw-contents \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{
    "title": "Mon article sur Docker",
    "content": "Voici mes notes brutes sur Docker Compose et microservices Laravel...",
    "source_type": "raw",
    "blueprint_id": '"$BLUEPRINT_ID"'
  }' -w "\nTemps total: %{time_total}s\n"
```

➡️ Temps attendu **< 100ms** (le job tourne en arrière-plan via la queue).

---

## 2. Validation invalide → 422

```
POST http://localhost:8000/api/raw-contents
```

**Headers**
```
Accept: application/json
Content-Type: application/json
Authorization: Bearer {{TOKEN}}
```

**Body** (content trop court)
```json
{"content": "trop court", "source_type": "raw", "blueprint_id": 1}
```

```bash
curl -s -X POST http://localhost:8000/api/raw-contents \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"content": "trop court", "source_type": "raw", "blueprint_id": 1}' | jq
```

➡️ Erreur `content` doit faire au moins 20 caractères.

---

## 3. Consultation → 200

```
GET http://localhost:8000/api/raw-contents/1
```

**Headers**
```
Accept: application/json
Authorization: Bearer {{TOKEN}}
```

```bash
curl -s http://localhost:8000/api/raw-contents/1 \
  -H "Accept: application/json" -H "Authorization: Bearer $TOKEN" | jq
```

➡️ Le champ `posts` doit apparaître avec `body_points` et `suggested_hashtags` déjà en tableau.

---

## 4. Sans token → 401

```
GET http://localhost:8000/api/raw-contents/1
```

**Headers**
```
Accept: application/json
```

```bash
curl -s http://localhost:8000/api/raw-contents/1 \
  -H "Accept: application/json" | jq
```

---

## 5. Workflow complet (création → queue → vérification)

**Étape 1 — Création**
```bash
curl -s -X POST http://localhost:8000/api/raw-contents \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{
    "title": "Mon article sur Docker",
    "content": "Voici mes notes brutes sur Docker Compose et microservices Laravel...",
    "source_type": "raw",
    "blueprint_id": '"$BLUEPRINT_ID"'
  }' -w "\nTemps total: %{time_total}s\n"
```

➡️ Vérifie que le temps est **< 100ms**.

**Étape 2 — Surveiller le worker**
```bash
docker compose logs -f queue
```

➡️ Attends que le job soit terminé (le log affiche "Post generated successfully" ou similaire).

**Étape 3 — Vérifier le résultat**
```bash
curl -s http://localhost:8000/api/raw-contents/1 \
  -H "Accept: application/json" -H "Authorization: Bearer $TOKEN" | jq
```

➡️ `posts` doit être rempli avec `body_points` (tableau de strings) et `suggested_hashtags` (tableau de strings).
