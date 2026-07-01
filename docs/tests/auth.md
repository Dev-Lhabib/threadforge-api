# Auth API — Tests

**Variables**
```bash
TOKEN="ton_token"
```

---

## 1. Inscription (valide → 201)

**Postman**
```
POST http://localhost:8000/api/register
```

**Headers**
```
Accept: application/json
Content-Type: application/json
```

**Body**
```json
{
  "name": "Jane",
  "email": "jane@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

**Curl**
```bash
curl -s -X POST http://localhost:8000/api/register \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -d '{
    "name": "Jane",
    "email": "jane@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }' | jq
```

➡️ Retourne `user` + `token`. Conserve le token pour les tests suivants.

---

## 2. Inscription invalide → 422

**Postman**
```
POST http://localhost:8000/api/register
```

**Headers**
```
Accept: application/json
Content-Type: application/json
```

**Body**
```json
{"name": "", "email": "bad", "password": "123"}
```

**Curl**
```bash
curl -s -X POST http://localhost:8000/api/register \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -d '{"name": "", "email": "bad", "password": "123"}' | jq
```

➡️ Erreurs de validation sur `name`, `email`, `password`.

---

## 3. Connexion (valide → 200)

**Postman**
```
POST http://localhost:8000/api/login
```

**Headers**
```
Accept: application/json
Content-Type: application/json
```

**Body**
```json
{
  "email": "jane@example.com",
  "password": "password123"
}
```

**Curl**
```bash
curl -s -X POST http://localhost:8000/api/login \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -d '{"email": "jane@example.com", "password": "password123"}' | jq
```

➡️ Retourne `user` + `token`.

---

## 4. Connexion invalide → 422

**Postman**
```
POST http://localhost:8000/api/login
```

**Headers**
```
Accept: application/json
Content-Type: application/json
```

**Body**
```json
{
  "email": "jane@example.com",
  "password": "wrongpassword"
}
```

**Curl**
```bash
curl -s -X POST http://localhost:8000/api/login \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -d '{"email": "jane@example.com", "password": "wrongpassword"}' | jq
```

➡️ Message d'erreur : "Les identifiants fournis sont incorrects."

---

## 5. Profil utilisateur (GET /me → 200)

**Postman**
```
GET http://localhost:8000/api/me
```

**Headers**
```
Accept: application/json
Authorization: Bearer {{TOKEN}}
```

**Curl**
```bash
curl -s http://localhost:8000/api/me \
  -H "Accept: application/json" -H "Authorization: Bearer $TOKEN" | jq
```

---

## 6. Sans token → 401

**Postman**
```
GET http://localhost:8000/api/me
```

**Headers**
```
Accept: application/json
```

**Curl**
```bash
curl -s http://localhost:8000/api/me \
  -H "Accept: application/json" | jq
```

---

## 7. Déconnexion → 200

**Postman**
```
POST http://localhost:8000/api/logout
```

**Headers**
```
Accept: application/json
Authorization: Bearer {{TOKEN}}
```

**Curl**
```bash
curl -s -X POST http://localhost:8000/api/logout \
  -H "Accept: application/json" -H "Authorization: Bearer $TOKEN" | jq
```

➡️ Retourne `{"message": "Déconnecté avec succès."}`. Le token est révoqué.

---

## 8. Utiliser un token révoqué → 401

**Postman**
```
GET http://localhost:8000/api/me
```

**Headers**
```
Accept: application/json
Authorization: Bearer {{TOKEN_AVEC_UNE_SEULE_ESPACE}}
```

**Curl**
```bash
curl -s http://localhost:8000/api/me \
  -H "Accept: application/json" -H "Authorization: Bearer $TOKEN" | jq
```

➡️ Après déconnexion, le même token doit retourner `401`.
