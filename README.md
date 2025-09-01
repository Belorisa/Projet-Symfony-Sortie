# Projet-Symfony-Sortie


Groupe 8 : Marie-Laure, Pierre, Damione



# Application de gestion de sorties

Une application web Symfony 6.4 pour gérer des événements ("sorties"), avec inscription des utilisateurs, rappels par email, et gestion administrateur.

---

## Fonctionnalités

- Inscription et gestion des profils utilisateurs
- Création, modification et suppression de sorties
- Inscription et désinscription aux sorties
- Envoi automatique d’un email de rappel 48 heures avant le début d’une sortie
- Interface administrateur pour gérer les utilisateurs et les sorties
- Upload de fichiers pour les photos de profil
- Envoi d’emails asynchrones via Symfony Messenger

---

## Stack technique

- **PHP 8.3+**
- **Symfony 6.4.24**
- **Doctrine ORM**
- **Twig** pour les templates
- **Symfony Messenger** pour la messagerie asynchrone
- **Symfony Mailer** pour l’envoi d’emails
- **MySQL**
- **Bootstrap**

---

## Installation

### 1. Cloner le projet :

```bash
git clone https://github.com/username/sortie-app.git
cd sortie-app
```

### 2. Installer les dépendances :
```bash
composer install
```

### 3. Configuration des variables d'environnement :
```bash
DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/sortie_db"(adresse de votre DB local)
MAILER_DSN="smtp://username:password@smtp.mailtrap.io:2525"(si vous utiliser mailHog)
```

### 4.Créer la base de données :
```bash
symfony console doctrine:database:create
symfony console doctrine:migrations:migrate
```

### 5.Lancer le serveur Symfony :

```bash
symfony serve(depuis un invite de commande executer dans le dossier du projet )
```

## Utilisation

### Exécution du worker Messenger
Pour traiter les emails asynchrones

```bash
 symfony console messenger:consume async
 ```

### Interface administrateur
* Route : /admin/user_list

* Permet la gestion des utilisateurs : activer/désactiver/supprimer/ajouter via CSV

* Requiert le rôle administrateur (ROLE_ADMIN)

### Workflow des sorties

1. Les utilisateurs créent une sortie avec date de début/fin et date limite d’inscription,un lieu et des détails.
2. Les utilisateurs peuvent s’inscrire ou se désinscrire à la sortie.
3. Les participants reçoivent un email de confirmation immédiatement.
4. Un email de rappel est automatiquement envoyé 48 heures avant le début de la sortie.

### Notes pour le développement
* La gestion des dates et heures est basée sur UTC en interne ; l’affichage est converti dans les templates Twig si nécessaire.

* Les fichiers uploadés (photos de profil) sont stockés dans le dossier défini par sortie.photos_directory dans services.yaml.

* Les messages pour les rappels sont stockés en base jusqu’à leur traitement. Il est possible d’activer la suppression automatique (auto_delete) après envoi.

### Commandes utiles
* Créer la DB et migrer : php bin/console doctrine:migrations:migrate
* Lancer le serveur : symfony serve
* Consommer les messages Messenger :  symfony console messenger:consume async
* Retenter les messages échoués : symfony messenger:failed:retry all
* Supprimer les messages échoués : symfony messenger:failed:remove
