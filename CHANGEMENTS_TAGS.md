# Changements apportés - Système de Tags et Améliorations Révision

## 🎯 Modifications principales

### 1. Système de Tags
- **Tags sur les cartes** : Les tags sont maintenant associés aux cartes, pas aux decks
- **Relation many-to-many** : Une carte peut être dans plusieurs decks
- **Decks comme tags spéciaux** : Les decks sont vus comme des tags au format `deck/nom`
- **Table `cartes_tags`** : Relation many-to-many entre cartes et tags
- **Table `cartes_decks`** : Relation many-to-many entre cartes et decks

### 2. Système de Révision amélioré
- ✅ **Ordre aléatoire** : Les questions sont présentées dans un ordre aléatoire
- ✅ **Pas de limite quotidienne** : On peut refaire une séance de révision à tout moment
- ✅ **Sessions non-terminées** : Possibilité de reprendre une session en cours
- ✅ **Durée et taux de succès** : L'historique affiche maintenant la durée et le taux de succès de chaque session

### 3. Sessions de Révision
- **Table `sessions_revision`** : Stocke les informations de chaque session
  - Date de début/fin
  - Durée en secondes
  - Nombre de cartes révisées
  - Nombre de bonnes/mauvaises réponses
  - Taux de succès
  - Statut (en_cours, terminee, abandonnee)

### 4. Méta-Tags
- **Composition de tags** : Créer des decks via composition/exclusion de tags
- **Types de composition** :
  - `et` : Toutes les cartes qui ont TOUS les tags inclus
  - `ou` : Toutes les cartes qui ont AU MOINS UN des tags inclus
  - `sauf` : Toutes les cartes SAUF celles qui ont les tags exclus

## 📋 Migration nécessaire

Pour appliquer ces changements, exécutez :
```bash
php migration_tags.php
```

Ce script va :
1. Créer les nouvelles tables (tags, cartes_tags, cartes_decks, sessions_revision, meta_tags)
2. Migrer les données existantes vers la nouvelle structure
3. Ajouter la colonne `session_id` à l'historique des révisions

## 🔄 Changements dans les modèles

### Card.php
- `creerTexte()` et `creerQCM()` acceptent maintenant un paramètre `$tags`
- `obtenirParDeck()` utilise maintenant la relation many-to-many
- Nouvelles méthodes : `ajouterADeck()`, `retirerDeDeck()`, `obtenirDecks()`

### Nouveaux modèles
- **Tag.php** : Gestion des tags
- **SessionRevision.php** : Gestion des sessions de révision
- **MetaTag.php** : Gestion des méta-tags

## 🎮 Nouveau système de révision

### Workflow
1. L'étudiant sélectionne un deck
2. Une session est créée (ou reprise si une session en cours existe)
3. Les cartes sont présentées dans un ordre aléatoire
4. Chaque réponse est enregistrée avec la session_id
5. À la fin, la session est terminée avec calcul automatique de la durée et du taux de succès

### URLs
- Liste des decks : `/index.php?controller=revision&action=index`
- Démarrer/Reprendre : `/index.php?controller=revision&action=demarrer&deck_id=X&reprendre=1`
- Session active : `/index.php?controller=revision&action=session&session_id=X`
- Résultat : `/index.php?controller=revision&action=resultat&session_id=X`

## 📊 Historique amélioré

L'historique des révisions affiche maintenant :
- **Durée** : Temps total de la session
- **Taux de succès** : Pourcentage de bonnes réponses
- **Nombre de cartes** : Total révisé
- **Date** : Date et heure de la session

## 🏷️ Gestion des tags

### Créer un tag
Les tags sont créés automatiquement lors de l'ajout d'une carte.

### Voir les cartes d'un tag
Utiliser `Tag::obtenirCartesParTag($tag_id)`

### Créer un deck via méta-tags
1. Créer un méta-tag avec composition/exclusion
2. Le méta-tag retourne automatiquement les cartes correspondantes
3. Ces cartes peuvent être utilisées pour créer un deck dynamique

## ⚠️ Notes importantes

- Les anciennes cartes gardent leur `deck_id` pour compatibilité
- La migration crée automatiquement les relations dans `cartes_decks`
- Les sessions en cours peuvent être reprises à tout moment
- Le système de tags est optionnel : les cartes peuvent exister sans tags

