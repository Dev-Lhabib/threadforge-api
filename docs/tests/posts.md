# Posts API — Tests

## 1. Liste des posts (avec filtre status)

```
GET http://localhost:8000/api/posts?status=draft
```

**Headers**
```
Accept: application/json
Authorization: Bearer {{TOKEN}}
```

---

## 2. Détail d'un post

```
GET http://localhost:8000/api/posts/1
```

**Headers**
```
Accept: application/json
Authorization: Bearer {{TOKEN}}
```

---

## 3. Changer le statut (US6)

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

---

## 4. Accès cross-user → 403

Avec le token d'un **autre** utilisateur que celui qui a soumis le `raw_content`
ayant généré ce post.