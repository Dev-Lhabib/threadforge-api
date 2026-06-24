# Blueprints API — Tests

## 1. Création (valide → 201)

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

---

## 2. Validation invalide → 422

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

---

## 3. Liste

```
GET http://localhost:8000/api/blueprints
```

**Headers**
```
Accept: application/json
Authorization: Bearer {{TOKEN}}
```

---

## 4. Sans token → 401

```
GET http://localhost:8000/api/blueprints
```

**Headers**
```
Accept: application/json
```

---

## 5. Mise à jour partielle → 200

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

---

## 6. Suppression → 204

```
DELETE http://localhost:8000/api/blueprints/1
```

**Headers**
```
Accept: application/json
Authorization: Bearer {{TOKEN}}
```
