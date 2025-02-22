# HACKATHON_ESGIS

## Description du projet

Ce projet a pour but de créer un **site de rencontre** dynamique. Il permet aux utilisateurs de s'inscrire, de se connecter, et de trouver des correspondances selon différents critères. Le backend utilise PHP, et le frontend est géré via des fichiers HTML/PHP dynamiques.

## Technologies utilisées

- **Frontend** : HTML, CSS, JavaScript, Lucide (pour les icones), tailwind (framework CSS)
- **Backend** : PHP
- **Base de données** : MySQL
- **Serveur local** : Laragon ou XAMPP (environnement local de développement)
- **Contrôle de version** : Git (GitHub)

## Prérequis

Avant de pouvoir exécuter ce projet, assurez-vous d'avoir installé les éléments suivants sur votre machine :

1. **Laragon** ou **XAMPP** (pour exécuter un serveur local PHP et MySQL)
2. **Git** (pour gérer les versions et récupérer le projet)
3. Un éditeur de code comme **Visual Studio Code** ou **Windsurf**

## Installation

### Étape 0 : Préparations

1. Ouvrez votre navigateur et allez à l'adresse suivante :
[link](https://github.com/Florent242/HACKATHON_ESGIS.git)

2. Cliquer sur **Fork** et ensuite suivez les instructions pour fork au niveau de votre compte GitHub.

3. Ouvrez votre terminal et allez dans le dossier cloné ou vous souhaitez exécuter le projet.

### Étape 1 : Cloner le projet

Clonez le repository GitHub du projet dans votre répertoire local avec la commande suivante :

```bash
git clone https://github.com/votre_utilisateur_gitHub/HACKATHON_ESGIS.git
```
(si vous utilisez GitHub Desktop, cliquez sur **Clone or download** et suivez les instructions)

#### NB: 
**Nous vous recommandons de cloner le projet dans un dossier distinct de votre environnement de travail. Et surtout veiller a ce que votre fork soit tout le temps a jour avec le projet original.**
**Soumettez une pull request avec une description claire de vos changements.**

## Installation

### Étape 2 : Configuration de l'environnement

1. **Base de données** :
    - Importez le fichier **`database/schema.sql`** dans votre base de données MySQL via PhpMyAdmin ou en utilisant la commande `mysql` :
    ```bash
    mysql -u root -p < database/schema.sql
    ```
    - Assurez-vous de mettre à jour les informations de connexion dans le fichier **`.env.example`**. Copiez ce fichier et renommez-le en `.env`.
    - Exemple de fichier `.env` :
    ```env
    DB_HOST=localhost
    DB_NAME=nom_de_votre_base_de_donnees
    DB_USER=root
    DB_PASS=
    ```

2. **Serveur local** : 
    - Si vous utilisez **Laragon** ou **XAMPP**, assurez-vous de démarrer Apache et MySQL.
    - Placez le dossier cloné dans le répertoire approprié pour que le serveur local puisse y accéder (ex. `C:/laragon/www/HACKATHON_ESGIS` pour Laragon).

### Étape 3 : Configuration de l'environnement (optionnel pour les frontend)

1. **Copiez le fichier `.env.example`** et renommez-le en `.env`.
2. Mettez à jour les informations de connexion à la base de données dans le fichier `.env` :
    ```env
    DB_HOST=localhost
    DB_NAME=nom_de_votre_base_de_donnees
    DB_USER=root
    DB_PASS=
    ```

3. **Vérification de la configuration** :
    - Assurez-vous que **Apache** et **MySQL** sont démarrés sur Laragon ou XAMPP.
    - Vérifiez si la base de données est correctement configurée et accessible.

### Étape 4 : Accéder au site

1. **Démarrez le serveur local** avec Laragon ou XAMPP.
2. Ouvrez votre navigateur et allez à l'adresse suivante :
    ```
    http://localhost/HACKATHON_ESGIS/public/
    ```
3. Si tout est bien configuré, la page d'accueil du site devrait s'afficher.

### Étape 5 : Test

1. Vérifiez que toutes les fonctionnalités de base sont opérationnelles :
    - Inscription des utilisateurs
    - Connexion
    - Affichage des profils et correspondances
2. Si un problème survient, consultez le fichier de log de votre serveur (Laragon ou XAMPP) pour voir s'il y a des erreurs PHP ou des problèmes de connexion à la base de données.
3. Si vous souhaitez ajouter de nouvelles fonctionnalités, assurez-vous de créer une branche Git distincte pour ne pas perturber la version stable.

---

## Structure du projet

Voici la structure de répertoires du projet :
~~~
HACKATHON_ESGIS/
│
├── .env.example          # Fichier d'exemple pour les variables d'environnement
├── .git/                 # Dossier de contrôle de version pour Git
├── .gitignore            # Fichier spécifiant les fichiers à ignorer par Git
├── README.md             # Fichier de documentation du projet
│
├── backend/              # Dossier contenant le code backend
│   ├── api.php           # Point d'entrée de l'API
│   ├── controllers/      # Dossier pour les contrôleurs
│   ├── models/           # Dossier pour les modèles
│   ├── routes/           # Dossier pour les routes de l'API
│   └── services/         # Dossier pour les services
│
├── config/               # Dossier pour les fichiers de configuration
│
├── database/             # Dossier pour les fichiers liés à la base de données
│
├── frontend/             # Dossier contenant le code frontend
│   ├── home.php          # Page d'accueil
│   ├── login.php         # Page de connexion
│   ├── admin/      # Dossier des admin
│   └── user/       # Dossier des utilisateurs
│
├── includes/             # Dossier pour les fichiers inclus ou partagés
│
└── public/               # Dossier pour les fichiers accessibles publiquement
    ├── css/              # Fichiers CSS
    ├── js/               # Fichiers JavaScript
    └── images/           # Images publiques
~~~

## Contribuer

Si vous souhaitez contribuer au projet, suivez ces étapes :

1. **Forkez** le repository.
2. **Créez une nouvelle branche** avec un nom pertinent (par exemple : `feature-nouvelle-fonctionnalite`).
3. Apportez vos modifications et **testez-les localement**.
4. **Soumettez une pull request** avec une description claire de vos changements.

### Bonnes pratiques pour contribuer

- **Commit messages** : Soyez descriptif dans vos messages de commit. Par exemple, "Ajout d'une fonctionnalité de recherche par critères".
- **Tests** : Avant de soumettre une PR, assurez-vous que vos modifications sont bien testées sur votre serveur local.
- **Code propre** : Respectez les conventions de codage (indentation, nommage des variables et des fichiers) pour que le code soit lisible et maintenable par tous.

## Contibuteurs

- ADJASSE Maurel : `morelvlto93@gmail.com` - Développeur principal, Responsable de l'interface utilisateur et coordinateur de l'equipe frontend.
- Florent BOUDZOUMOU : `florentboudz@gmail.com` - Coordinateur de l'équipe backend et responsable du design du site.
- Adechina : - Responsable de la conception et du développement backend.
- AÏHOUNHIN Eunock  : `izumishinishi7@gmail.com` - Responsable de la conception de la base de données et du développement backend.
- AZANDOSSESSI Kénel : `azandossessik@gmail.com` - Acteur du développement forntend.
- SOMAKPO Eliot : `eliotsomakpo@gmail.com` - Acteur du développement frontend.
- Immaculée ODJO : `immac045320@gmail.com` - Acteur du développement frontend.

## Contact

Si vous avez des questions ou des suggestions, n'hésitez pas à contacter ADJASSE Maurel à l'adresse email suivante : `morelvlto93@gmail.com`.
Ou sur le numéro whatsapp : `+229 01 99 87 12 67`

---

Ce projet est un travail collaboratif au sein du groupe **HACKATHON_ESGIS**.
