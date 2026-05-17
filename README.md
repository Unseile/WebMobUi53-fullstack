# HEIG-VD DévProdMéd Course - Mini-projet

Ce dépôt contient le mini-projet à réaliser dans le cadre du cours
_"[Développement de produit média (DévProdMéd)](https://github.com/heig-vd-devprodmed-course/heig-vd-devprodmed-course)"_
enseigné à la
[Haute Ecole d'Ingénierie et de Gestion du Canton de Vaud (HEIG-VD)](https://heig-vd.ch),
Suisse.

## Objectif du mini-projet

L'objectif de ce mini-projet est de créer un réseau social simple en utilisant le
framework [Laravel](https://laravel.com/). Ce projet permettra de mettre en pratique les concepts
appris dans le cours.

## Pré-requis

Afin de lancer ce projet, une stack compatible avec Laravel, est requise.

Voici les pré-requis nécessaires :

- PHP >= 8.0.
- Composer.
- Node.js et npm.
- Une base de données (MySQL, PostgreSQL, SQLite, etc.).
- Un serveur web (Apache, Nginx, etc.).

[Laravel Herd](https://helm.sh/docs/charts/laravel/) est recommandé pour une installation facile de Laravel et de ses dépendances.

## Développement local

Pour développer et tester le mini-projet en local, voici les étapes à suivre :

1. Forker ce dépôt

2. Installer les dépendances avec npm et Composer :

    ```bash
    npm install && npm run build

    composer install
    ```

3. Copier le fichier `.env.example` en `.env`.
4. Modifier les variables d'environnement si nécessaire (optionnel).
5. Générer la clé d'application Laravel :

    ```bash
    php artisan key:generate
    ```

6. Créer le lien symbolique pour les fichiers téléversés :

    ```bash
    php artisan storage:link
    ```

7. Créer la base de données et exécuter les migrations :

    ```bash
    php artisan migrate
    ```

    S'il est nécessaire de réinitialiser la base de données, utiliser la commande `php artisan migrate:reset` puis `php artisan migrate` à nouveau.

8. Optionnel : en mode développement, il est possible de peupler la base de données avec des données fictives :

    ```bash
    php artisan db:seed
    ```

9. Démarrer le serveur de développement Laravel :

    ```bash
    composer run dev
    ```

L'application sera accessible à l'adresse <http://localhost:8000>.

## Choix techniques

### Le frontend est construit autour de composants Vue.js :

PollTable.vue — affiche la liste des sondages de l'utilisateur connecté
CreatePoll.vue — formulaire de création d'un sondage
EditPoll.vue — formulaire de modification d'un sondage
ShowPoll.vue — page de vote et d'affichage des résultats

* useHashRoute (déjà fourni) : utilisé pour la navigation entre les vues.
* useFetchApi (déjà fourni) : centralise Tous les appels HTTP vers ce composable. Cela m'a permis d'éviter de répéter la même logique dans chaque composant.
* usePolling (déjà fourni) : rafraîchit automatiquement les résultats d'un sondage toutes les 5 secondes via des appels réguliers à l'API.


### Les modifications dans le backend :

#### Modèles

Poll : Ajout d'un $fillable pour les champs title, question, secret_token, is_draft, allow_multiple_choices, allow_vote_change, results_public, duration, started_at et ends_at

PollOption : Ajout d'un $fillable pour le champ label.

PollVote : Ajout d'un $fillable pour les champs poll_id, user_id et poll_option_id


#### Contrôleur

ApiPollController

index() : retourne la liste des sondages de l'utilisateur connecté. Charge les options associées avec with('options') et sélectionne uniquement les colonnes nécessaires pour éviter de retourner des données inutiles.

show() : Affiche un sondage via son secret_token public. Charge les options avec le nombre de votes via withCount('votes'). Vérifie si l'utilisateur connecté a déjà voté et retourne cette information dans la réponse via already_voted.

store() : Crée un nouveau sondage. Valide les données reçues, génère un secret_token unique via Str::uuid(), et crée les options associées via une boucle.

update() : Modifie un sondage existant. Vérifie que le sondage existe et que l'utilisateur en est bien le propriétaire avant des modifications.

destroy() : Supprime un sondage.

vote() : Enregistre un vote. Cela enregistre un PollVote par option sélectionnée et retourne les options avec le nombre de votes mis à jour.

results() : Retourne les options d'un sondage avec leur nombre de votes. Ajout de headers Cache-Control pour éviter que le navigateur mette en cache les résultats.

#### Routes

Les routes API :

* Routes publiques : pour afficher un sondage via son token de partage, pour consulter les résultats publics.
* Routes protégées sous le middleware auth:sanctum : pour la création, la modification, la suppression des sondages et la soumission des votes.

Les routes web :

* Routes publiques : pour permettre à n'importe qui d'accéder à la page de vote d'un sondage via son token.