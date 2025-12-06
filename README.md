<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

# Centiva – Laravel API (Test technique Full-stack)

Cette application constitue la portion **backend Laravel** du test technique demandé par Centiva.
Elle implémente un système simple de gestion des **équipes** et **courtiers**, conformément aux spécifications fournies dans le document officiel.

---

## Pourquoi je n'ai pas retenu Docker pour ce projet

Bien que Docker ait été évoqué lors de l’entrevue, il a été volontairement exclu de ce livrable.
En contexte de test technique, la priorité est de fournir un environnement :

- simple à cloner ;
- rapide à exécuter ;
- sans dépendances lourdes ;
- sans risque d’erreurs liées aux scripts d’automatisation.

Un setup Docker aurait nécessité un dépôt supplémentaire, un Dockerfile, un script d’installation automatisée (setup.sh), ainsi que la gestion de plusieurs clones Git, ce qui augmente la complexité et peut nuire à l’expérience d’évaluation.

Le choix délibéré a donc été de proposer un frontend Nuxt 3 et un backend Laravel totalement autonomes, faciles à démarrer et transparents à tester.

---

## Fonctionnalités principales

### ✔ Gestion des équipes (Teams)
- Lister toutes les équipes (avec leurs courtiers associés)
- Afficher une équipe spécifique via son identifiant
- Soft-delete d’une équipe (avec soft-delete en cascade des courtiers liés)

### ✔ Gestion des courtiers (Brokers)
- Lister tous les courtiers (avec leur équipe)
- Afficher un courtier spécifique
- Créer un courtier **dans une équipe donnée** (via une route imbriquée REST)

### ✔ Architecture & bonnes pratiques Laravel
- Relations Eloquent (`Team` ↔ `Broker`)
- **Soft Deletes** sur les deux modèles
- **Form Requests** pour la validation
- **JSON Resources** pour un rendu d'API contrôlé (Ex: Ne pas exposer les ID, les dates de création, modification, soft-delete, etc.)
- **Routes RESTful claires**
- **Seeders** pour générer des données de démonstration

---

## Installation & exécution locale

### 1. Cloner le projet
```bash
git clone https://github.com/ibanson/centiva-laravel-api.git
cd centiva-laravel-api
```

### 2. Installer les dépendances backend
```bash
composer install
```

### 3. Préparer le fichier d’environnement
```bash
cp .env.example .env
php artisan key:generate
```

Configurer SQLite dans `.env` :
```
DB_CONNECTION=sqlite
DB_DATABASE=./database/database.sqlite
```

Créer le fichier SQLite (si absent) :
```bash
touch database/database.sqlite
```

### 4. Lancer les migrations + données de démo
```bash
php artisan migrate --seed
```

### 5. Démarrer le serveur Laravel
```bash
php artisan serve
```

L’API sera disponible à l’adresse :
```
http://127.0.0.1:8000/api
```

---

## Endpoints API disponibles

### 🟦 **Équipes (Teams)**

| Méthode | Route | Description |
|---------|--------|-------------|
| GET | `/api/teams` | Liste toutes les équipes (+ brokers) |
| GET | `/api/teams/{id}` | Détails d’une équipe |
| DELETE | `/api/teams/{id}` | Soft-delete d’une équipe + cascade brokers |

---

### 🟩 **Courtiers (Brokers)**

| Méthode | Route | Description |
|---------|--------|-------------|
| GET | `/api/brokers` | Liste tous les courtiers |
| GET | `/api/brokers/{id}` | Détails d’un courtier |
| POST | `/api/teams/{team}/brokers` | Créer un courtier dans une équipe |

#### Exemple de payload POST
```json
{
  "name": "Laurent Decottegnie",
  "email": "laurent.decottegnie@gmail.com"
}
```

---

## Structure du projet

```
app/
 ├── Http/
 │    ├── Controllers/Api/
 │    ├── Requests/Api/
 │    └── Resources/Api/
 ├── Models/
database/
 ├── migrations/
 ├── seeders/
 └── database.sqlite (non versionné)
routes/
 └── api.php
```

---

## Données de démonstration

Le seeder fournit automatiquement :
- plusieurs **équipes**
- des **courtiers assignés**
- des données cohérentes pour tester immédiatement les endpoints

---

## Conformité avec les exigences du test

- [x] 4 endpoints GET
- [x] 1 endpoint POST
- [x] 1 endpoint DELETE (soft-delete + cascade)
- [x] Modèles + migrations
- [x] Soft Deletes
- [x] Relations Team/Broker
- [x] Validation des données
- [x] JSON Resources
- [x] Seeds de démo