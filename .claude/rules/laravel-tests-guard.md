---
requiert: phpunit.xml
description: Garde-fous des tests Laravel (sqlite fichier, ordre de la garde, transactions Mockery). Rule PARTAGEE, source dans dev-standards.
paths:
  - "tests/**"
  - "phpunit.xml"
  - "database/migrations/**"
---
<!-- dev-standards-sha256: ce15450648b2391eb9266c566531a843775974f79c566911d6a4fb602c203c8b -->

<!-- FICHIER DEPLOYE DEPUIS dev-standards/rules/laravel-tests-guard.md.
     DO NOT EDIT THIS COPY: bin/rules-deploy refuses a copy edited in place.
     Make changes in the dev-standards source, then redeploy. --force replaces
     a refused copy after backing it up to .orig (or .orig.N); it never
     writes through a symbolic link.
     Anything specific to THIS project belongs in a neighbouring rule, not here. -->

# Garde-fous des tests Laravel, NE JAMAIS AFFAIBLIR

Un wipe réel de la base de dev a eu lieu le **2026-07-17** sur
laurentdecottegnie : `php artisan test` a lancé `migrate:fresh` sur pgsql.
Autospa portait le même défaut d'ordre dans sa garde et n'a échappé au wipe que
grâce à son `tests/bootstrap.php`.

Ce fichier réunit ce que **cinq projets** ont appris séparément. Chaque
enseignement a été payé une fois quelque part ; aucun ne doit se reperdre.

## Les trois couches, toutes obligatoires

**1. `tests/bootstrap.php`** (référencé par `bootstrap=` dans `phpunit.xml`)
écrase `$_SERVER` **+** `$_ENV` **+** `putenv` en sqlite AVANT tout chargement
de Laravel.

> Indispensable parce que le `force="true"` de `phpunit.xml` n'écrit **pas**
> dans `$_SERVER`, or docker compose y injecte la connexion pgsql/mysql et
> Laravel lit `$_SERVER` en priorité. **`phpunit.xml` seul est contournable.**

**2. `phpunit.xml`** : sqlite **fichier** (`/tmp/testing.sqlite`), `force="true"`,
jamais `:memory:`.

> Deux raisons, et la seconde est celle qu'on découvre trop tard :
> - `:memory:` **bloque silencieusement** avec `RefreshDatabase` sur Docker
>   WSL2 : la suite ne se termine jamais.
> - `:memory:` rend la suite **solidaire d'un seul test**. La base n'existe que
>   dans la connexion qui l'a ouverte, donc Laravel garde le même PDO pour tout
>   le process : une transaction laissée ouverte par un test y survit et fait
>   échouer tous les suivants sur `There is already an active transaction`.
>   **Mesuré chez autospa le 2026-08-09 : 3 bugs réels produisaient 126
>   échecs.** Le passage au fichier a isolé les vrais coupables (8 échecs).

**3. `tests/TestCase.php`** : la garde vérifie la connexion **AVANT**
`parent::setUp()` : booter l'app avec `refreshApplication()`, puis vérifier.

> C'est `parent::setUp()` qui déclenche le `migrate:fresh` de
> `RefreshDatabase`. **Une garde placée après constate le wipe au lieu de
> l'empêcher.** C'est la nuance qui sépare un garde-fou d'un témoin.

## Le piège du `force` ne concerne pas que la base

`phpunit.xml` avec `force="true"` n'écrit que dans `$_ENV` et `putenv`,
**jamais dans `$_SERVER`**, que docker compose remplit et que Laravel lit en
**premier**. Ce n'est donc pas une particularité de `DB_CONNECTION` : **tout**
réglage que l'environnement docker injecte échappe à `phpunit.xml`.

> Vécu chez ibanson le 2026-08-18 : la suite entière tournait en `APP_ENV=local`
> alors que `phpunit.xml` déclarait `testing`. **Ajouter `force="true"` n'a rien
> changé** ; c'est le `bootstrap.php` qui a tranché, en posant la valeur dans
> les trois canaux. Conséquence concrète : les drapeaux de développement étaient
> actifs pendant les tests.
>
> Même forme le 2026-08-17 : un `SHOPIFY_TRACE=true` du `.env` de la machine a
> activé un traçage qui écrivait en base dans les tests sans `RefreshDatabase`,
> et faisait tomber les suivants.

**Règle générale : tout drapeau de développement se coupe dans le bootstrap,
jamais par confiance dans l'environnement.**

## Mockery, et les transactions qu'il laisse ouvertes

- **Matcher par id, jamais l'instance** : `Mockery::on(fn ($m) => $m->id === $attendu->id)`.
  Deux instances Eloquent de même contenu sont des objets différents.
- **Ne jamais redéfinir `tearDown()` avec `Mockery::close()`** : Laravel s'en
  charge via `MockeryPHPUnitIntegration`, **après** le rollback de
  `RefreshDatabase`. Fermer soi-même le fait avant, et une attente non
  satisfaite lève au milieu de la transaction : base verrouillée.
  > Et la surcharge **retire des vérifications** : mesuré chez ibanson le
  > 2026-08-18 en supprimant la dernière, le compte d'assertions est monté de
  > **63 à 66**. Laravel comptabilise les attentes Mockery au moment de sa
  > propre fermeture ; fermer à la main l'en empêche.
- **Une attente `->once()` non satisfaite laisse une transaction ouverte.**
  Mockery lève à la fermeture, donc avant le rollback. Le symptôme n'apparaît
  pas sur le test fautif mais sur **le suivant**, en
  `There is already an active transaction` ou `database is locked`.

## Lire une vague d'échecs

Quand plusieurs tests tombent avec `active transaction` ou `database is locked`,
chercher le **PREMIER** rouge de l'exécution, pas les suivants : ce sont des
victimes. Un test qui échoue seulement dans la suite complète et passe avec
`--filter` est un test **pollué** : trouver le pollueur au lieu de l'ajuster.

## Le `/tmp` du conteneur est volatil, et c'est au bootstrap de le savoir

Le fichier sqlite disparaît à chaque redémarrage de Docker. **Le
`tests/bootstrap.php` doit donc le recréer lui-même** (`touch` s'il n'existe
pas), et non compter sur un geste manuel : une doc qui demande de le recréer à
la main vieillit mal, on la lit le jour où la suite est déjà rouge.

> Vérifié le 2026-09-04 : les bootstraps de laurentdecottegnie et d'autospa le
> font tous les deux. La doc d'autospa demandait encore un `touch` manuel alors
> que son propre code s'en chargeait depuis un moment.

Un fichier qui disparaît n'est jamais une raison de **changer de stratégie**
(revenir à `:memory:`, viser un autre chemin) : c'est une raison de vérifier que
le bootstrap fait son travail.

## Ce que ces tests ne disent PAS

⚠⚠ Ils tournent sur **SQLite**, la production sur **PostgreSQL**. Une suite
verte ne dit donc **rien** des comportements propres à PG : types stricts,
contraintes différées, verrous, casse des identifiants, requêtes non portables.
Le garde-fou anti-wipe est la raison de ce choix et il n'est pas négociable,
mais il a ce prix : ce qui dépend vraiment de Postgres se vérifie autrement
(un test d'intégration dédié, ou un contrôle sur la base réelle).

Enseignement remonté de vigie, où le seul contrôle qui comptait pour les
licences était de vérifier qu'elles se re-vérifient après un aller-retour de
dump PG, chose qu'aucun test SQLite n'aurait pu dire.

## Spécificités d'un projet

Ce fichier est **partagé** : tout ce qui ne vaut que pour un projet (tables de
couverture, traits d'authentification, nombre de tests, commande de lancement)
va dans une rule voisine du même dossier, jamais ici.
