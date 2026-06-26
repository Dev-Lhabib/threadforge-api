# Chat Agent — Tests

## 1. Zero hallucination — vérifier l'appel réel du Tool

```
POST http://localhost:8000/api/posts/1/chat
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
  "message": "Quelles sont les règles de mon blueprint actuel pour ce post ?"
}
```

**Vérification** : surveiller `docker compose logs -f app` ou écouter l'event
`InvokingTool` (Laravel\Ai\Events\InvokingTool) — il doit apparaître avec
`GetCampaignRules` comme tool invoqué. La réponse textuelle doit correspondre
exactement aux valeurs réelles du blueprint en base (pas des valeurs inventées).

---

## 2. Continuité de conversation (US8)

### Q1 — Traduire le post

```
POST http://localhost:8000/api/posts/1/chat
```

**Body**
```json
{
  "message": "Traduis ce post en anglais."
}
```

→ Noter le `conversation_id` retourné dans la réponse.

### Q2 — Référence implicite ("celui-ci")

```
POST http://localhost:8000/api/posts/1/chat
```

**Body**
```json
{
  "message": "Donne-moi un autre hook pour celui-ci.",
  "conversation_id": "{{CONVERSATION_ID_DE_Q1}}"
}
```

**Vérification** : la réponse doit porter sur la **version traduite en anglais**
de Q1, preuve que le contexte est bien rechargé depuis `agent_conversations` /
`agent_conversation_messages`.

---

## 3. Sauvegarder une réponse comme nouvelle version

```
POST http://localhost:8000/api/posts/1/chat
```

**Body**
```json
{
  "message": "Réécris ce post avec un ton plus direct.",
  "save_as_version": true
}
```

**Vérification** : `version_id` non-null dans la réponse, et une nouvelle ligne
visible dans `post_versions` via phpMyAdmin.