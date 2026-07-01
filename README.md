# ThreadForge API

API headless (Laravel 13) de **repurposing de contenu tech** pour créateurs sur X (Twitter). Transforme automatiquement des notes brutes, articles de blog ou README GitHub en posts optimisés, en respectant des règles de style réutilisables (Blueprints), et propose un assistant conversationnel (Ghostwriter Agent) pour affiner le contenu généré.

## Sommaire

- [Stack technique](#stack-technique)
- [Architecture](#architecture)
- [Prérequis](#prérequis)
- [Installation](#installation)
- [Variables d'environnement](#variables-denvironnement)
- [Lancer le projet](#lancer-le-projet)
- [Documentation API](#documentation-api)
- [Authentification](#authentification)
- [Fonctionnalités](#fonctionnalités)
- [Modèle de données](#modèle-de-données)
- [Tests manuels](#tests-manuels)
- [Problèmes connus & dépannage](#problèmes-connus--dépannage)
- [Workflow Git](#workflow-git)
- [Structure du projet](#structure-du-projet)

---

## Stack technique

| Composant | Technologie |
|---|---|
| Framework | Laravel 13 |
| Langage | PHP 8.3 |
| Base de données | MySQL 8.0 |
| Cache / Queue | Redis 7 |
| Authentification | Laravel Sanctum |
| IA | `laravel/ai` SDK (provider Groq, modèle `meta-llama/llama-4-scout-17b-16e-instruct`) |
| Documentation API | Scribe (knuckleswtf/scribe) |
| Conteneurisation | Docker Compose (sans Dockerfile — images officielles uniquement) |
| Administration DB | phpMyAdmin |

> ℹ️ Le cahier des charges initial prévoyait l'API **xAI/Grok**. L'intégration a été réalisée et validée techniquement, mais le compte xAI utilisé ne disposait pas de crédits actifs (erreur `403 permission-denied`). Le projet a basculé sur **Groq** (tier gratuit), le SDK `laravel/ai` permettant ce changement via un simple attribut PHP (`#[Provider(Lab::Groq)]`), sans modifier la logique métier.

## Architecture

```
                         ┌──────────────┐
                         │   Postman /  │
                         │   Client API │
                         └──────┬───────┘
                                │ HTTP (Bearer Token)
                                ▼
                    ┌───────────────────────┐
                    │   app (php artisan     │
                    │   serve, port 8000)    │
                    └─────────┬─────────────┘
                              │
              ┌───────────────┼────────────────┐
              ▼               ▼                ▼
        ┌──────────┐   ┌────────────┐   ┌────────────┐
        │  mysql   │   │   redis    │   │ phpmyadmin │
        │ (8.0)    │   │ (queue)    │   │  (8081)    │
        └──────────┘   └─────┬──────┘   └────────────┘
                              │
                              ▼
                    ┌───────────────────────┐
                    │  queue (php artisan    │
                    │  queue:work redis)     │
                    │  → GeneratePostJob     │
                    │  → PostGeneratorAgent  │
                    │  → API Groq            │
                    └───────────────────────┘
```

**Principe clé** : `POST /api/raw-contents` répond en **moins de 100ms** (202 Accepted) — l'appel à l'IA est entièrement délégué à un Job Laravel (`GeneratePostJob`) traité de façon asynchrone par un worker dédié, découplé du cycle de requête HTTP.

## Prérequis

- Docker & Docker Compose v2
- PHP 8.3 + Composer installés en local (uniquement pour le scaffolding initial via `laravel new` — l'exécution se fait ensuite entièrement dans Docker)
- Une clé API [Groq](https://console.groq.com) (gratuite)

## Installation

```bash
git clone <url-du-repo> threadforge-api
cd threadforge-api
```

Copie le fichier d'environnement et configure tes variables (voir section suivante) :

```bash
cp .env.example .env
```

Lance l'environnement Docker :

```bash
docker compose up -d
```

> ⚠️ Le premier démarrage installe les extensions PHP (`pdo_mysql`, `mbstring`, `zip`, `gd`, `bcmath`, `redis`) à la volée — comptez 1 à 2 minutes. Surveillez la progression avec `docker compose logs -f app` et attendez le statut `healthy` (`docker compose ps`) avant d'exécuter des commandes `artisan`.

Génère la clé applicative et lance les migrations :

```bash
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
```

## Variables d'environnement

```env
APP_NAME=ThreadForge_API
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=threadforge
DB_USERNAME=sail
DB_PASSWORD=password

QUEUE_CONNECTION=redis
REDIS_HOST=redis
REDIS_CLIENT=phpredis

GROQ_API_KEY=ta_clé_groq_ici
```

> ⚠️ **Piège fréquent** : `QUEUE_CONNECTION` et `REDIS_HOST` doivent **toujours** correspondre aux noms des services Docker (`redis`, `mysql`), jamais à `127.0.0.1` — sauf si la commande est exécutée hors Docker (ce qui n'est jamais le cas ici). Si vous modifiez ces valeurs en cours de route, videz le cache de config : `php artisan config:clear`.

## Lancer le projet

| Service | URL |
|---|---|
| API | http://localhost:8000/api |
| Documentation Scribe | http://localhost:8000/docs |
| phpMyAdmin | http://localhost:8081 (login `root` / `root`) |

Toutes les commandes Artisan passent par Docker :

```bash
docker compose exec app php artisan <commande>
```

## Documentation API

La documentation interactive complète (endpoints, paramètres, exemples de requêtes/réponses, testeur intégré) est générée par **Scribe** :

```bash
docker compose exec app php artisan scribe:generate
```

→ disponible sur **http://localhost:8000/docs**

## Authentification

L'API utilise **Laravel Sanctum** (Bearer Token).

```bash
# Inscription
curl -X POST http://localhost:8000/api/register \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -d '{"name":"Jane","email":"jane@example.com","password":"password123","password_confirmation":"password123"}'

# Connexion
curl -X POST http://localhost:8000/api/login \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -d '{"email":"jane@example.com","password":"password123"}'
```

Le token retourné s'utilise ensuite dans le header :
```
Authorization: Bearer {token}
```

## Fonctionnalités

| Domaine | Endpoints |
|---|---|
| **Authentification** | `POST /register`, `POST /login`, `POST /logout`, `GET /me` |
| **Blueprints** (règles de style réutilisables) | `GET/POST /blueprints`, `GET/PATCH/DELETE /blueprints/{id}` |
| **Raw Contents** (soumission de contenu brut) | `POST /raw-contents`, `GET /raw-contents/{id}` |
| **Posts** (cycle de vie éditorial) | `GET /posts`, `GET /posts/{id}`, `PATCH /posts/{id}` |
| **Chat** (Ghostwriter Agent) | `POST /posts/{id}/chat` |

### Flux de génération (US4/US5)

1. Le créateur soumet un contenu brut + un `blueprint_id` → `POST /raw-contents`
2. L'API répond **202 Accepted** immédiatement
3. `GeneratePostJob` (queue Redis) instancie `PostGeneratorAgent`, qui appelle l'IA avec un schéma JSON strict (`hook_propose`, `body_points`, `technical_readability_score`, `suggested_hashtags`, `tone_compliance_justification`)
4. Le contrat de réponse est validé avant insertion ; les champs JSON sont normalisés (anti double-encodage)
5. Un nouveau `Post` est créé, lié au `RawContent` et au `Blueprint` utilisés

### Chat contextuel (US7/US8/US9)

L'agent `GhostwriterAgent` dispose de deux **Tools** réels (pas d'hallucination) :
- `GetCampaignRules` — lit les vraies règles du Blueprint en base
- `GetPostHistory` — lit l'historique réel des versions du post

La mémoire de conversation (US8) est gérée nativement par le SDK (`RemembersConversations`), sans code custom — les tables `agent_conversations` / `agent_conversation_messages` sont créées automatiquement.

## Modèle de données

5 entités métier (MCD validé) :

```
USER ──crée──< BLUEPRINT
USER ──soumet──< RAW_CONTENT ──génère──< POST >──utilise── BLUEPRINT
POST ──possède──< POST_VERSION
```

| Table | Rôle |
|---|---|
| `users` | Comptes créateurs (Sanctum) |
| `blueprints` | Règles de style (ton, max hashtags, max caractères...) |
| `raw_contents` | Contenu brut soumis par l'utilisateur |
| `posts` | Sortie structurée générée par l'IA |
| `post_versions` | Historique des éditions via le chat |

> Les tables `agent_conversations` / `agent_conversation_messages` (mémoire IA) sont des tables techniques gérées par le SDK, volontairement exclues du MCD métier.

### MCD

![MCD](docs/mcd.png)

### MLD

![MLD](docs/mld.png)

## Tests manuels

Les scénarios de test sont versionnés en Markdown (testables manuellement ou via Postman) dans `docs/tests/` :

```
docs/tests/
├── auth.md
├── blueprints.md
├── raw-contents.md
├── posts.md
└── chat.md
```

## Problèmes connus & dépannage

| Symptôme | Cause | Solution |
|---|---|---|
| `Post::latest()` reste `null` après soumission | `QUEUE_CONNECTION` ne correspond pas à la connexion écoutée par le worker (`queue:work redis`) | Vérifier `config('queue.default')` via Tinker → doit être `"redis"` ; sinon `php artisan config:clear` + `docker compose restart queue` |
| `RedisException: Connection refused (127.0.0.1)` | `REDIS_HOST` pointe vers `127.0.0.1` au lieu du nom du service Docker | `REDIS_HOST=redis` dans `.env`, puis `config:clear` |
| `could not find driver` juste après `docker compose up` | Race condition — les extensions PHP sont encore en cours d'installation | Attendre le statut `healthy` (`docker compose ps`) avant toute commande `artisan` |
| `install failed` sur `pecl install redis` au redémarrage | L'extension est déjà installée mais la commande n'est pas idempotente | Le `command:` du service encapsule l'installation dans `(php -m \| grep -q redis \|\| (pecl install redis && docker-php-ext-enable redis))` |
| `This model does not support response format json_schema` | Le modèle Groq configuré ne supporte pas les Structured Outputs | Vérifier la liste des modèles compatibles sur [console.groq.com/docs/structured-outputs](https://console.groq.com/docs/structured-outputs) (la liste évolue) et ajuster l'attribut `#[Model(...)]` de l'agent concerné |
| `403 permission-denied` côté xAI | Compte xAI sans crédits actifs | Basculer temporairement sur Groq via `#[Provider(Lab::Groq)]` |

## Workflow Git

- `main` : branche stable, jamais de commit direct après le Sprint 1
- Une branche par sprint (`feature/blueprints`, `feature/generation`, `feature/chat`...), fusionnée via Pull Request (**Merge commit**, jamais de Squash — pour préserver l'historique de commits atomiques)
- Convention de commit : `type(scope): description` (`feat`, `fix`, `refactor`, `chore`, `docs`, `test`)

```bash
git checkout main && git pull
git checkout -b feature/ma-feature
# ... travail + commits atomiques ...
git push origin feature/ma-feature
# Ouvrir la PR sur GitHub → Merge commit
```

## Structure du projet

```
app/
├── Ai/
│   ├── Agents/          # PostGeneratorAgent, GhostwriterAgent
│   └── Tools/           # GetCampaignRules, GetPostHistory
├── Http/
│   ├── Controllers/     # Auth, Blueprint, RawContent, Post, Chat
│   ├── Requests/        # Form Requests par domaine
│   └── Resources/       # API Resources (anti fuite de données)
├── Jobs/
│   └── GeneratePostJob.php   # Génération IA asynchrone
├── Models/
└── Policies/            # Ownership (Blueprint, RawContent, Post)

docs/
├── mcd.png             # Modèle Conceptuel de Données
├── mld.png             # Modèle Logique de Données
└── tests/              # Scénarios de tests manuels (Markdown)

docker-compose.yml        # app, mysql, phpmyadmin, redis, queue
```

---

**Livrables associés** : Jira (sprints TF-S1 à TF-S5), MCD/MLD (`docs/mcd.png`, `docs/mld.png`), documentation Scribe (`/docs`), historique Git (≥20 commits atomiques).
