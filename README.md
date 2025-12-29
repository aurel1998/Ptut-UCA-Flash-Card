# UCA Flash Card - Plateforme de Révision

Plateforme de révision basée sur le système Leitner permettant aux étudiants de l'Université Clermont-Auvergne de réviser efficacement avec des flashcards (cartes texte et QCM). Les enseignants peuvent créer des decks de cartes et les assigner aux étudiants, tandis que les étudiants peuvent créer leurs propres decks, les partager entre eux et réviser quotidiennement.

## Installation locale

### Prérequis

- **PHP 7.4 ou supérieur** (avec extension PDO SQLite)
- Aucune base de données externe nécessaire (SQLite est intégré à PHP)

### Installation rapide

1. ** Vous devez télécharger ou cloner le projet**
   ```bash
   git clone https://github.com/aurel1998/Ptut-UCA-Flash-Card.git
   cd Ptut-UCA-Flash-Card
   ```

2. **Initialiser la base de données**
   ```bash
   php init_db.php
   ```
   Cette commande crée automatiquement le fichier de base de données SQLite et toutes les tables nécessaires. Il faut exécuter cette commande dans le dossier du projet télechargé

3. **Lancer le serveur**
   - **Windows** : Double-cliquez sur `start.bat` ou Exécutez `php -S localhost:8001 router.php`
   - **Linux/Mac** : Exécutez `php -S localhost:8001 router.php`

4. **Accéder à l'application**
   Ouvrez votre navigateur et allez à : `http://localhost:8001`

 Le fichier de base de données est dans le projet, facile à sauvegarder ou partager
 SQLite est intégré à PHP, fonctionne sur Windows, Linux et Mac

Pour un guide d'installation détaillé, consultez [INSTALLATION.md](INSTALLATION.md).
