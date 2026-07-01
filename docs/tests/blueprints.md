# Blueprints API — Tests

**Variables**
```bash
TOKEN="ton_token"
```

---

## 1. Création (valide → 201)

**Postman**
```
POST http://localhost:8000/api/blueprints
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
  "name": "Tech Community Aggressive",
  "tone": "professionnel mais décontracté",
  "max_hashtags": 1,
  "max_characters": 280,
  "additional_rules": "Pas de buzzwords corporate"
}
```

**Curl**
```bash
curl -s -X POST http://localhost:8000/api/blueprints \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{
    "name": "Tech Community Aggressive",
    "tone": "professionnel mais décontracté",
    "max_hashtags": 1,
    "max_characters": 280,
    "additional_rules": "Pas de buzzwords corporate"
  }' | jq
```

---

## 2. Validation invalide → 422

**Postman**
```
POST http://localhost:8000/api/blueprints
```

**Headers**
```
Accept: application/json
Content-Type: application/json
Authorization: Bearer {{TOKEN}}
```

**Body**
```json
{"name": ""}
```

**Curl**
```bash
curl -s -X POST http://localhost:8000/api/blueprints \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"name": ""}' | jq
```

➡️ Erreur : `name` est obligatoire.

---

## 3. Liste

**Postman**
```
GET http://localhost:8000/api/blueprints
```

**Headers**
```
Accept: application/json
Authorization: Bearer {{TOKEN}}
```

**Curl**
```bash
curl -s http://localhost:8000/api/blueprints \
  -H "Accept: application/json" -H "Authorization: Bearer $TOKEN" | jq
```

---

## 4. Sans token → 401

**Postman**
```
GET http://localhost:8000/api/blueprints
```

**Headers**
```
Accept: application/json
```

**Curl**
```bash
curl -s http://localhost:8000/api/blueprints \
  -H "Accept: application/json" | jq
```

---

## 5. Mise à jour partielle → 200

**Postman**
```
PATCH http://localhost:8000/api/blueprints/1
```

**Headers**
```
Accept: application/json
Content-Type: application/json
Authorization: Bearer {{TOKEN}}
```

**Body**
```json
{"tone": "ironique et technique"}
```

**Curl**
```bash
curl -s -X PATCH http://localhost:8000/api/blueprints/1 \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"tone": "ironique et technique"}' | jq
```

---

## 6. Suppression → 204

**Postman**
```
DELETE http://localhost:8000/api/blueprints/1
```

**Headers**
```
Accept: application/json
Authorization: Bearer {{TOKEN}}
```

**Curl**
```bash
curl -s -o /dev/null -w "%{http_code}" -X DELETE http://localhost:8000/api/blueprints/1 \
  -H "Accept: application/json" -H "Authorization: Bearer $TOKEN"
```

➡️ Doit retourner `204` (aucun contenu).
