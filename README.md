# Évaluation Symfony n°2 – Agenda d'événements : tests et nouvelles fonctionnalités

Application de gestion d'événements (concerts, conférences, ateliers, sport) avec Symfony 7.4.

Ce dépôt contient **la correction de l'évaluation n°1** : événements, inscriptions, comptes et validation fonctionnent déjà. Dans cette évaluation, vous allez :

1. mettre en place un **environnement de test** (fixtures, base de test, `make tests`) ;
2. ajouter une **nouvelle page** « Mes inscriptions » ;
3. écrire des **tests unitaires** et des **tests fonctionnels** avec PHPUnit.

Le design est **déjà fait** : le HTML de la nouvelle page est en dur dans `templates/event/mine.html.twig`, à vous de le rendre dynamique **sans vous occuper du CSS**.

Référence : *Symfony : The Fast Track*, chapitre 17 « Tester » : <https://symfony.com/doc/7.4/the-fast-track/fr/17-tests.html>

---

## ⚠️ Avant de commencer : forkez le dépôt

> **Ne commitez et ne poussez JAMAIS sur ce dépôt.** Vous travaillez uniquement sur **un nouveau fork** de ce dépôt.
>
> Ne reprenez **pas** votre fork de l'évaluation n°1 (`eval_b2_symfony`) et ne le supprimez pas. L'évaluation n°2 est un **autre dépôt**, qui part de la correction de l'évaluation n°1.

1. Rendez-vous sur <https://github.com/louvelmathieu/eval2_b2_symfony> et cliquez sur le bouton **Fork** en haut à droite.

   > Votre fork doit être **public** : c'est lui qui sera corrigé. Utilisez bien le bouton **Fork** (le fork d'un dépôt public est public par défaut) et ne faites pas de copie privée du projet.

2. Clonez **votre fork** (et non le dépôt d'origine), dans un **nouveau dossier** :

   ```bash
   git clone https://github.com/<votre-compte>/eval2_b2_symfony.git
   cd eval2_b2_symfony
   ```

3. Vérifiez que `origin` pointe bien vers **votre** compte :

   ```bash
   git remote -v
   ```

4. Commitez régulièrement sur votre fork, avec des messages clairs.

---

## 📦 Rendu

Le rendu se fait sur **votre fork** (branche `main`). Avant la date limite, complétez ce bloc et commitez-le :

```text
Nom       : ...
Prénom    : ...
```

La correction se fera en lançant `make tests` : **tous les tests doivent passer** (`OK` en vert). Les comptes de test sont créés par vos fixtures (voir partie 1), inutile de commiter une base de données.

---

## Installation

### Prérequis

- PHP **8.3** ou plus, avec l'extension `pdo_sqlite` (`php -m | grep sqlite` pour vérifier)
- [Composer](https://getcomposer.org/)
- [Symfony CLI](https://symfony.com/download) (recommandé)
- `make` (sous Windows : `scoop install make`)

### Étapes

```bash
composer install
php bin/console doctrine:migrations:migrate
symfony server:start
```

Le site est accessible sur <https://127.0.0.1:8000>. Sans Symfony CLI : `php -S 127.0.0.1:8000 -t public`

### Base de données

Le projet utilise **SQLite** : chaque environnement a son propre fichier.

| Environnement | Fichier | Utilisé par |
|---|---|---|
| `dev` | `var/data_dev.db` | le site dans votre navigateur |
| `test` | `var/data_test.db` | PHPUnit (`APP_ENV=test`, défini dans `phpunit.dist.xml`) |

> `doctrine:database:create` ne fonctionne pas avec SQLite et n'est pas nécessaire : `doctrine:migrations:migrate` crée le fichier.

---

## Ce qui est fourni

| Fichier | Contenu |
|---|---|
| `src/`, `templates/` | La correction de l'évaluation n°1 |
| `templates/event/mine.html.twig` | HTML statique de la page « Mes inscriptions » |
| `templates/base.html.twig` | Lien « Mes inscriptions » du menu, en commentaire |

---

## Sujet

### Partie 1 – Environnement de test

1. Installez les fixtures et DAMA DoctrineTestBundle :

   ```bash
   composer require --dev orm-fixtures "dama/doctrine-test-bundle:^8"
   ```

   La recette de DAMA ne s'installe pas automatiquement sur ce projet. Faites ces deux étapes à la main :
   - dans `config/bundles.php`, ajoutez `DAMA\DoctrineTestBundle\DAMADoctrineTestBundle::class => ['test' => true],`
   - dans `phpunit.dist.xml`, entre `<extensions>` et `</extensions>`, ajoutez `<bootstrap class="DAMA\DoctrineTestBundle\PHPUnit\PHPUnitExtension"/>`

   > DAMA annule à la fin de chaque test tout ce qu'il a écrit en base : chaque test repart des fixtures, sans dépendre des autres.

2. Dans `src/DataFixtures/AppFixtures.php`, créez **exactement** ces données (les tests en dépendent) :

   **Utilisateurs** (mots de passe hashés avec `UserPasswordHasherInterface`)

   | Email | Mot de passe | Rôle |
   |---|---|---|
   | `admin@test.fr` | `admin123` | `ROLE_ADMIN` |
   | `user@test.fr` | `user123` | – |

   **Événements** (utilisez des dates relatives, ex. `new \DateTime('+10 days')`, pour qu'ils restent « à venir »)

   | Titre | Date | Catégorie | Places | Validé | Inscrits |
   |---|---|---|---|---|---|
   | Soirée jazz | dans 10 jours | concert | 80 | oui | – |
   | Semi-marathon | dans 15 jours | sport | 300 | oui | user |
   | Initiation à la poterie | dans 20 jours | atelier | 1 | oui | admin (→ complet) |
   | Concert passé | il y a 10 jours | concert | 80 | oui | – |
   | Tournoi de basket | dans 30 jours | sport | 10 | **non** | – |

3. Créez un `Makefile` avec une cible `tests` qui recrée la base de test, charge les fixtures puis lance PHPUnit, comme dans le cours (voir Astuces pour la version SQLite).

### Partie 2 – Nouvelle page « Mes inscriptions »

4. Créez la page `/mes-inscriptions` (route `app_my_events`, template `event/mine.html.twig`) :
   - elle est réservée aux utilisateurs **connectés** ;
   - elle affiche le **nombre** d'événements auxquels l'utilisateur connecté est inscrit, puis la liste de ces événements (titre cliquable vers la page de détail, date, catégorie avec sa couleur) ;
   - chaque événement a un bouton « **Se désinscrire** » ;
   - « Vous n'êtes inscrit à aucun événement pour le moment. » quand il n'y en a aucun.

   > La relation existe déjà : `$this->getUser()->getEvents()` donne les événements de l'utilisateur connecté.

5. Dans le menu, décommentez le lien « **Mes inscriptions** » : il n'est affiché qu'aux utilisateurs connectés.

6. **Pas de code dupliqué** :
   - le HTML d'une carte d'événement ne doit exister qu'**une seule fois**. Déplacez-le dans `templates/event/_card.html.twig` et utilisez-le avec `{{ include() }}` sur l'accueil et sur « Mes inscriptions ». Le bouton « Se désinscrire » ne s'affiche que sur « Mes inscriptions » ;
   - le bouton « Se désinscrire » utilise la route de désinscription **qui existe déjà** (pas de nouvelle route) ; après la désinscription, l'utilisateur revient sur la page d'où il vient (détail **ou** « Mes inscriptions »), avec le message flash.

   Doc : <https://symfony.com/doc/7.4/templates.html#including-templates>

### Partie 3 – Tests unitaires (`TestCase`)

7. `tests/Entity/EventTest.php` :
   - teste `getRemainingPlaces()` avec un **data provider** d'au moins 3 cas : aucun inscrit, quelques inscrits, complet ;
   - teste `getCategoryLabel()` avec un **data provider** sur les 4 catégories (ex. `conference` → `Conférence`).

### Partie 4 – Tests fonctionnels (`WebTestCase`)

Une méthode de test par point au minimum, répartie dans un ou plusieurs fichiers de `tests/Controller/`.

8. **Accueil** : la page répond, affiche **3** événements (`.event-card`), et « Concert passé » et « Tournoi de basket » n'y sont pas.
9. **Catégories** : `/?categorie=sport` n'affiche qu'un événement, et le lien « Sport » a la classe `active`.
10. **Détail** : depuis l'accueil, un clic sur « Soirée jazz » (`clickLink`) affiche son titre dans le `h1`. `/evenement/9999` renvoie une **404**.
11. **Ajout d'un événement** : soumettez le formulaire (`submitForm`) **avec une image** ; vérifiez la redirection, le message flash, et que l'événement n'apparaît **pas** sur l'accueil (il n'est pas validé).
12. **Comptes** : la création de compte puis la connexion avec le formulaire de `/login` affichent « Se déconnecter » dans le menu ; un mauvais mot de passe affiche « Identifiants invalides ».
13. **Inscription** : connecté avec `user@test.fr`, l'inscription à « Soirée jazz » fait passer les places restantes à **79**. La désinscription du « Semi-marathon » fait remonter ses places à **300**.
14. **Complet** : « Initiation à la poterie » affiche le badge `.badge-full` et pas de bouton « S'inscrire ».
15. **Validation** : `/validate` redirige un visiteur vers `/login` et renvoie **403** à `user@test.fr`. Un admin valide « Tournoi de basket », qui apparaît ensuite sur l'accueil.
16. **Mes inscriptions** :
    - un visiteur est redirigé vers `/login` ;
    - `user@test.fr` voit **1** événement : « Semi-marathon » ;
    - après s'être inscrit à « Soirée jazz », il en voit **2** ;
    - « Se désinscrire » depuis « Mes inscriptions » ramène sur `/mes-inscriptions`, avec le message flash, et l'événement n'y est plus ;
    - « Se désinscrire » depuis la page de détail ramène toujours sur la page de détail ;
    - le lien « Mes inscriptions » est dans le menu une fois connecté, et absent pour un visiteur.

**`make tests` doit afficher `OK` : aucun test en échec.**

---

## Barème indicatif

| Partie | Points |
|---|---|
| 1 – Environnement de test (fixtures, DAMA, Makefile) | 4 |
| 2 – Page « Mes inscriptions » (4 à 6) | 4 |
| 3 – Tests unitaires (7) | 3 |
| 4 – Tests fonctionnels (8 à 16) | 9 |

---

## Astuces

### Commandes utiles

```bash
php bin/console make:test          # choisir TestCase ou WebTestCase
php bin/console doctrine:fixtures:load            # charge les fixtures en dev
php bin/phpunit                                   # lance les tests
php bin/phpunit --filter testHome                 # lance un seul test
```

### Makefile pour SQLite

Comme dans le cours, sans `doctrine:database:create` qui ne marche pas avec SQLite. **L'indentation doit être une tabulation**, pas des espaces.

```makefile
tests:
	php bin/console doctrine:database:drop --force --env=test || true
	php bin/console doctrine:migrations:migrate -n --env=test
	php bin/console doctrine:fixtures:load -n --env=test
	php bin/phpunit $(MAKECMDGOALS)
.PHONY: tests
```

### Tests fonctionnels

```php
// Se connecter sans passer par le formulaire
$user = static::getContainer()->get(UserRepository::class)->findOneBy(['email' => 'user@test.fr']);
$client->loginUser($user);

// Compter des éléments
$crawler = $client->request('GET', '/');
$this->assertCount(4, $crawler->filter('.event-card'));

// Envoyer un fichier dans un formulaire : donnez son chemin
'event[image]' => dirname(__DIR__, 2).'/public/images/sport.jpg',
```

- Assertions utiles : `assertResponseIsSuccessful()`, `assertResponseStatusCodeSame(404)`, `assertResponseRedirects('/login')`, `assertSelectorTextContains()`, `assertSelectorExists()`, `assertSelectorNotExists()`, `assertAnySelectorTextContains()`.
- Après un `submitForm` qui redirige, pensez à `$client->followRedirect()` avant de vérifier la page.
- Revenir à la page précédente dans un contrôleur : `return $this->redirect($request->headers->get('referer'));`. Le `Referer` est une URL complète : dans les tests, vérifiez avec `assertResponseRedirects('http://localhost/mes-inscriptions')`.
- Le nom du bouton passé à `submitForm()` est son **texte** : `'Publier'`, `'S\'inscrire'`, `'Se connecter'`…
- Si vous modifiez vos entités ou vos fixtures, relancez `make tests` pour recréer la base de test.

### Tests unitaires

Un test unitaire n'utilise ni la base ni Symfony : on crée les objets à la main.

```php
$event = new Event();
$event->setPlaces(10);
$event->addParticipant(new User());

$this->assertSame(9, $event->getRemainingPlaces());
```
