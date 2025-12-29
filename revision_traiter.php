<?php
require_once 'config.php';
redirigerSiNonEtudiant();

$utilisateur = obtenirUtilisateurConnecte();
$connexion = obtenirConnexion();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: revision.php');
    exit;
}

$carte_id = $_POST['carte_id'] ?? 0;
$deck_id = $_POST['deck_id'] ?? 0;
$reponse_texte = trim($_POST['reponse_texte'] ?? '');
$reponse_choix = $_POST['reponse_choix'] ?? []; // Tableau pour plusieurs choix

if (empty($carte_id) || empty($deck_id)) {
    header('Location: revision.php');
    exit;
}

// Vérifier que cette carte n'a pas déjà été révisée aujourd'hui
$requete = $connexion->prepare("
    SELECT id FROM historique_revisions
    WHERE utilisateur_id = ? AND carte_id = ? AND deck_id = ?
    AND date(date_revision) = date('now')
    LIMIT 1
");
$requete->execute([$utilisateur['id'], $carte_id, $deck_id]);
$carte_deja_revisee = $requete->fetch();

if ($carte_deja_revisee) {
    // Cette carte a déjà été révisée aujourd'hui, on passe à la suivante
    header('Location: revision_session.php?deck_id=' . $deck_id);
    exit;
}

// Récupérer la carte
$requete = $connexion->prepare("SELECT * FROM cartes WHERE id = ?");
$requete->execute([$carte_id]);
$carte = $requete->fetch();

if (!$carte) {
    header('Location: revision.php');
    exit;
}

$resultat = 'incorrect';
$reponse_utilisateur = '';

// Nettoyer le tableau reponse_choix pour les cartes texte
if ($carte['type'] !== 'qcm') {
    $reponse_choix = [];
    // Vérifier que la réponse texte n'est pas vide
    if (empty($reponse_texte)) {
        header('Location: revision_session.php?deck_id=' . $deck_id . '&erreur=reponse_vide');
        exit;
    }
}

// Comparer la réponse selon le type de carte
if ($carte['type'] === 'qcm') {
    // Pour les QCM, vérifier si les choix sélectionnés sont corrects
    // Récupérer tous les choix de la carte
    $requete_tous_choix = $connexion->prepare("SELECT id, texte_choix, est_correct FROM choix_cartes WHERE carte_id = ?");
    $requete_tous_choix->execute([$carte_id]);
    $tous_choix = $requete_tous_choix->fetchAll();
    
    // Récupérer les IDs des choix corrects
    $choix_corrects_ids = [];
    $choix_incorrects_ids = [];
    foreach ($tous_choix as $choix_item) {
        if ($choix_item['est_correct']) {
            $choix_corrects_ids[] = $choix_item['id'];
        } else {
            $choix_incorrects_ids[] = $choix_item['id'];
        }
    }
    
    // Récupérer les choix sélectionnés par l'utilisateur
    $choix_selectionnes = is_array($reponse_choix) ? $reponse_choix : [];
    
    if (empty($choix_selectionnes)) {
        $resultat = 'incorrect';
        $reponse_utilisateur = 'Aucune réponse sélectionnée';
    } else {
        // Construire la réponse utilisateur
        $reponses_texte = [];
        foreach ($choix_selectionnes as $choix_id) {
            foreach ($tous_choix as $choix_item) {
                if ($choix_item['id'] == $choix_id) {
                    $reponses_texte[] = $choix_item['texte_choix'];
                    break;
                }
            }
        }
        $reponse_utilisateur = implode('; ', $reponses_texte);
        
        // Vérifier si toutes les bonnes réponses sont sélectionnées et aucune mauvaise
        $toutes_bonnes_selectionnees = count($choix_corrects_ids) > 0 && 
                                       count(array_intersect($choix_selectionnes, $choix_corrects_ids)) === count($choix_corrects_ids);
        $aucune_mauvaise_selectionnee = count(array_intersect($choix_selectionnes, $choix_incorrects_ids)) === 0;
        
        $resultat = ($toutes_bonnes_selectionnees && $aucune_mauvaise_selectionnee) ? 'correct' : 'incorrect';
    }
} else {
    // Pour les cartes texte, comparer la réponse de l'étudiant avec la réponse attendue
    $reponse_utilisateur = $reponse_texte;
    
    if (!empty($reponse_texte)) {
        // Normaliser les réponses : minuscules, supprimer espaces multiples
        $reponse_attendue = mb_strtolower(trim($carte['texte_verso']), 'UTF-8');
        $reponse_etudiant = mb_strtolower(trim($reponse_texte), 'UTF-8');
        
        // Supprimer les espaces multiples
        $reponse_attendue_norm = preg_replace('/\s+/', ' ', $reponse_attendue);
        $reponse_etudiant_norm = preg_replace('/\s+/', ' ', $reponse_etudiant);
        
        // Supprimer la ponctuation pour la comparaison
        $reponse_attendue_norm = preg_replace('/[^\w\s]/u', '', $reponse_attendue_norm);
        $reponse_etudiant_norm = preg_replace('/[^\w\s]/u', '', $reponse_etudiant_norm);
        
        // Comparaison exacte
        if ($reponse_etudiant_norm === $reponse_attendue_norm) {
            $resultat = 'correct';
        } else {
            // Comparaison avec similarité (au moins 85% de similarité)
            similar_text($reponse_attendue_norm, $reponse_etudiant_norm, $similarite);
            if ($similarite >= 85) {
                $resultat = 'correct';
            }
        }
    }
}

// Récupérer le statut Leitner actuel
$requete = $connexion->prepare("SELECT * FROM statuts_leitner WHERE utilisateur_id = ? AND carte_id = ?");
$requete->execute([$utilisateur['id'], $carte_id]);
$statut = $requete->fetch();

$pile_actuelle = $statut ? $statut['pile'] : 1;

// Calculer la nouvelle pile selon le système Leitner
if ($resultat === 'correct') {
    $nouvelle_pile = min($pile_actuelle + 1, 5);
} else {
    $nouvelle_pile = 1;
}

// Calculer la prochaine date de révision selon la pile
$intervalles = [
    1 => '+1 day',
    2 => '+2 days',
    3 => '+5 days',
    4 => '+14 days',
    5 => '+30 days'
];

$prochaine_revision = date('Y-m-d H:i:s', strtotime($intervalles[$nouvelle_pile]));

// Mettre à jour ou créer le statut Leitner
if ($statut) {
    $requete = $connexion->prepare("
        UPDATE statuts_leitner 
        SET pile = ?, prochaine_revision = ?, derniere_revision = datetime('now')
        WHERE id = ?
    ");
    $requete->execute([$nouvelle_pile, $prochaine_revision, $statut['id']]);
} else {
    $requete = $connexion->prepare("
        INSERT INTO statuts_leitner (utilisateur_id, carte_id, pile, prochaine_revision, derniere_revision)
        VALUES (?, ?, ?, ?, datetime('now'))
    ");
    $requete->execute([$utilisateur['id'], $carte_id, $nouvelle_pile, $prochaine_revision]);
}

// Enregistrer dans l'historique avec la réponse de l'utilisateur
$requete = $connexion->prepare("
    INSERT INTO historique_revisions (utilisateur_id, carte_id, deck_id, resultat, reponse_utilisateur)
    VALUES (?, ?, ?, ?, ?)
");
$requete->execute([$utilisateur['id'], $carte_id, $deck_id, $resultat, $reponse_utilisateur]);

// Stocker le résultat dans la session pour l'afficher
$_SESSION['revision_resultat'] = $resultat;
$_SESSION['revision_reponse_utilisateur'] = $reponse_utilisateur;

// Rediriger vers la page de résultat
header('Location: revision_resultat.php?carte_id=' . $carte_id . '&deck_id=' . $deck_id);
exit;

