<?php
require_once 'config.php';

// Rediriger vers le dashboard si connecté, sinon vers la page d'accueil
if (estConnecte()) {
    header('Location: dashboard.php');
} else {
    header('Location: accueil.php');
}
exit;

