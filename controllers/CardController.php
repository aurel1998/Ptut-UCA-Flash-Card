<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/Deck.php';
require_once __DIR__ . '/../models/Card.php';

class CardController {
    private $card;
    private $deck;
    
    public function __construct() {
        $this->card = new Card();
        $this->deck = new Deck();
    }
    
    // Afficher le formulaire d'ajout
    public function ajouter() {
        redirigerSiNonConnecte();
        
        $deck_id = $_GET['deck_id'] ?? 0;
        $utilisateur = obtenirUtilisateurConnecte();
        
        if (!$this->deck->appartientA($deck_id, $utilisateur['id'])) {
            header('Location: /index.php?controller=deck&action=liste');
            exit;
        }
        
        $deck = $this->deck->obtenirParId($deck_id);
        $erreur = '';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $type = $_POST['type'] ?? 'texte';
            $question = trim($_POST['question'] ?? '');
            $reponse = trim($_POST['reponse'] ?? '');
            
            if (empty($question)) {
                $erreur = 'La question est obligatoire';
            } elseif ($type === 'texte' && empty($reponse)) {
                $erreur = 'La réponse est obligatoire pour les cartes texte';
            } else {
                if ($type === 'texte') {
                    $this->card->creerTexte($deck_id, $question, $reponse);
                } else {
                    // QCM
                    $choix = [];
                    $choix_inputs = $_POST['choix'] ?? [];
                    $choix_corrects = $_POST['choix_correct'] ?? [];
                    
                    foreach ($choix_inputs as $index => $texte_choix) {
                        if (!empty(trim($texte_choix))) {
                            $choix[] = [
                                'texte' => trim($texte_choix),
                                'est_correct' => in_array($index, $choix_corrects)
                            ];
                        }
                    }
                    
                    if (empty($choix)) {
                        $erreur = 'Veuillez ajouter au moins un choix';
                    } elseif (empty($choix_corrects)) {
                        $erreur = 'Veuillez sélectionner au moins une réponse correcte';
                    } else {
                        $this->card->creerQCM($deck_id, $question, $choix);
                    }
                }
                
                if (empty($erreur)) {
                    header('Location: /index.php?controller=deck&action=voir&id=' . $deck_id);
                    exit;
                }
            }
        }
        
        require_once __DIR__ . '/../views/layouts/header.php';
        require_once __DIR__ . '/../views/cartes/ajouter.php';
        require_once __DIR__ . '/../views/layouts/footer.php';
    }
    
    // Modifier une carte
    public function modifier() {
        redirigerSiNonConnecte();
        
        $id = $_GET['id'] ?? 0;
        $carte = $this->card->obtenirParId($id);
        
        if (!$carte) {
            header('Location: /index.php?controller=deck&action=liste');
            exit;
        }
        
        $deck = $this->deck->obtenirParId($carte['deck_id']);
        $utilisateur = obtenirUtilisateurConnecte();
        
        if (!$this->deck->appartientA($deck['id'], $utilisateur['id'])) {
            header('Location: /index.php?controller=deck&action=liste');
            exit;
        }
        
        $erreur = '';
        $choix = [];
        
        if ($carte['type'] === 'qcm') {
            $choix = $this->card->obtenirChoix($id);
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $question = trim($_POST['question'] ?? '');
            $reponse = trim($_POST['reponse'] ?? '');
            
            if (empty($question)) {
                $erreur = 'La question est obligatoire';
            } elseif ($carte['type'] === 'texte' && empty($reponse)) {
                $erreur = 'La réponse est obligatoire pour les cartes texte';
            } else {
                if ($carte['type'] === 'texte') {
                    $this->card->modifierTexte($id, $question, $reponse);
                } else {
                    // QCM
                    $choix_data = [];
                    $choix_inputs = $_POST['choix'] ?? [];
                    $choix_corrects = $_POST['choix_correct'] ?? [];
                    
                    foreach ($choix_inputs as $index => $texte_choix) {
                        if (!empty(trim($texte_choix))) {
                            $choix_data[] = [
                                'texte' => trim($texte_choix),
                                'est_correct' => in_array($index, $choix_corrects)
                            ];
                        }
                    }
                    
                    if (empty($choix_data)) {
                        $erreur = 'Veuillez ajouter au moins un choix';
                    } elseif (empty($choix_corrects)) {
                        $erreur = 'Veuillez sélectionner au moins une réponse correcte';
                    } else {
                        $this->card->modifierQCM($id, $question, $choix_data);
                    }
                }
                
                if (empty($erreur)) {
                    header('Location: /index.php?controller=deck&action=voir&id=' . $deck['id']);
                    exit;
                }
            }
        }
        
        require_once __DIR__ . '/../views/layouts/header.php';
        require_once __DIR__ . '/../views/cartes/modifier.php';
        require_once __DIR__ . '/../views/layouts/footer.php';
    }
    
    // Supprimer une carte
    public function supprimer() {
        redirigerSiNonConnecte();
        
        $id = $_GET['id'] ?? 0;
        $carte = $this->card->obtenirParId($id);
        
        if ($carte) {
            $deck = $this->deck->obtenirParId($carte['deck_id']);
            $utilisateur = obtenirUtilisateurConnecte();
            
            if ($this->deck->appartientA($deck['id'], $utilisateur['id'])) {
                $this->card->supprimer($id);
            }
        }
        
        header('Location: /index.php?controller=deck&action=voir&id=' . ($carte ? $carte['deck_id'] : 0));
        exit;
    }
}

