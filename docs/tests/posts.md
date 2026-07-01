# Posts API — Tests

**Variables**
```bash
TOKEN="ton_token"
POST_ID=1
```

---

## 1. Liste des posts (avec filtre status)

**Postman**
```
GET http://localhost:8000/api/posts?status=draft
```

**Headers**
```
Accept: application/json
Authorization: Bearer {{TOKEN}}
```

**Curl**
```bash
curl -s "http://localhost:8000/api/posts?status=draft" \
  -H "Accept: application/json" -H "Authorization: Bearer $TOKEN" | jq
```

---

## 2. Détail d'un post

**Postman**
```
GET http://localhost:8000/api/posts/1
```

**Headers**
```
Accept: application/json
Authorization: Bearer {{TOKEN}}
```

**Curl**
```bash
curl -s http://localhost:8000/api/posts/$POST_ID \
  -H "Accept: application/json" -H "Authorization: Bearer $TOKEN" | jq
```

---

## 3. Changer le statut (US6)

**Postman**
```
PATCH http://localhost:8000/api/posts/1
```

**Headers**
```
Accept: application/json
Content-Type: application/json
Authorization: Bearer {{TOKEN}}
```

**Body**
```json
{"status": "posted"}
```

**Curl**
```bash
curl -s -X PATCH http://localhost:8000/api/posts/$POST_ID \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"status": "posted"}' | jq
```

---

## 4. Accès cross-user → 403

Avec le token d'un **autre** utilisateur que celui qui a soumis le `raw_content`
ayant généré ce post.

**Postman**
```
GET http://localhost:8000/api/posts/1
```

**Headers**
```
Accept: application/json
Authorization: Bearer {{OTHER_USER_TOKEN}}
```

**Curl**
```bash
OTHER_USER_TOKEN="autre_token"

curl -s http://localhost:8000/api/posts/$POST_ID \
  -H "Accept: application/json" -H "Authorization: Bearer $OTHER_USER_TOKEN" | jq
```

➡️ Doit retourner `403 Forbidden`.
