<?php
require_once 'config.php';

// Router simple que j'adapte pour mon MVC
$controller = $_GET['controller'] ?? 'accueil';
$action = $_GET['action'] ?? 'index';

// Liste de mes contrôleurs disponibles
$controllers = [
    'accueil' => 'AccueilController',
    'auth' => 'AuthController',
    'dashboard' => 'DashboardController',
    'deck' => 'DeckController',
    'card' => 'CardController',
    'revision' => 'RevisionController',
    'progression' => 'ProgressionController',
    'assignation' => 'AssignationController',
    'partage' => 'PartageController'
];

// Chargement de mon contrôleur
if (isset($controllers[$controller])) {
    $controllerClass = $controllers[$controller];
    $controllerFile = __DIR__ . '/controllers/' . $controllerClass . '.php';
    
    if (file_exists($controllerFile)) {
        require_once $controllerFile;
        $controllerInstance = new $controllerClass();
        
        if (method_exists($controllerInstance, $action)) {
            $controllerInstance->$action();
        } else {
            die("Action '$action' non trouvée dans $controllerClass");
        }
    } else {
        die("Contrôleur '$controllerClass' non trouvé");
    }
} else {
    die("Contrôleur '$controller' non trouvé");
}
