# Guide d'installation - UCA Flash Card

Ce guide explique comment installer et utiliser le projet sur votre machine.

## 📋 Prérequis

Le seul prérequis est d'avoir **PHP 7.4 ou supérieur** installé sur votre machine.

**Vérifier l'installation de PHP :**
```bash
php -v
```

Si PHP n'est pas installé :
- **Windows** : Téléchargez depuis [php.net](https://www.php.net/downloads.php) ou installez XAMPP
- **Linux** : `sudo apt install php` (Ubuntu/Debian) 
- **Mac** : `brew install php` (avec Homebrew)

> **Note** : SQLite est intégré à PHP par défaut, aucune installation supplémentaire n'est nécessaire !

## 📥 Télécharger le projet

### Option 1 : Via Git (recommandé)

```bash
git clone https://github.com/aurel1998/Ptut-UCA-Flash-Card.git
cd Ptut-UCA-Flash-Card
```

### Option 2 : Télécharger le ZIP

1. Allez sur https://github.com/aurel1998/Ptut-UCA-Flash-Card
2. Cliquez sur le bouton vert **"Code"** → **"Download ZIP"**
3. Extrayez l'archive dans un dossier de votre choix

## 🗄️ Initialiser la base de données

La base de données SQLite sera créée automatiquement. Il suffit d'exécuter :

```bash
php init_db.php
```

Cette commande va :
- ✅ Créer le dossier `database/` s'il n'existe pas
- ✅ Créer le fichier `database/projet_tutore.db`
- ✅ Créer toutes les tables nécessaires
- ✅ Configurer les index et clés étrangères


✓ Table 'utilisateurs' créée
✓ Table 'decks' créée
✓ Table 'cartes' créée
✓ Table 'choix_cartes' créée
✓ Table 'statuts_leitner' créée
✓ Table 'historique_revisions' créée
✓ Table 'partages_decks' créée
✓ Table 'assignations_decks' créée

✅ Base de données initialisée avec succès !
Le fichier de base de données se trouve dans : database/projet_tutore.db
```


## 🚀 Lancer le serveur

### Option 1 : Script Windows (recommandé sous Windows)

Double-cliquez sur le fichier `start.bat`. Le serveur démarrera automatiquement.

### Option 2 : Ligne de commande 

Ouvrez un terminal dans le dossier du projet et exécutez :

```bash
php -S localhost:8001 router.php
```

Vous devriez voir :
```
PHP x.x.x Development Server started at http://localhost:8001
Document root is: /chemin/vers/Ptut-UCA-Flash-Card
Press Ctrl-C to quit.
```

> **Important** : Ne fermez pas cette fenêtre tant que vous utilisez l'application.

##  Accéder à l'application

1. Ouvrez votre navigateur (Chrome, Firefox, Edge, etc.)
2. Allez à l'adresse : `http://localhost:8001`
3. Vous devriez voir la **page d'accueil** de l'application

## ✅ Créer un compte et tester

1. Cliquez sur **"S'inscrire"** ou allez sur `http://localhost:8001/register.php`
2. Remplissez le formulaire d'inscription :
   - Nom
   - Email
   - Mot de passe 
   - Confirmation du mot de passe
   - Rôle (Étudiant ou Enseignant)
   - Si voous avez choisi étudiant : vous devez entrer la Filière et votre Année
3. Cliquez sur **"S'inscrire"**
4. Connectez-vous avec vos identifiants
5. Testez l'application :
   - Créez un deck
   - Ajoutez des cartes (texte ou QCM)
   - Faites une révision

### Page blanche

**Causes possibles :**

### Port 8001 déjà utilisé

**Solution :**
1. Changez le port dans `start.bat` ou la commande :
   ```bash
   php -S localhost:8002 router.php
   ```
2. Accédez à l'application via `http://localhost:8002`

## 📁 Structure de la base de données

Le fichier de base de données SQLite se trouve dans :
```
database/projet_tutore.db
```

**Pour sauvegarder vos données :**
Copiez simplement le fichier `database/projet_tutore.db` !

**Pour réinitialiser la base de données :**
Supprimez `database/projet_tutore.db` et réexécutez `php init_db.php`

