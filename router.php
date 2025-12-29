<?php
// Router pour le serveur PHP intégré
// Ce fichier permet de gérer les routes et de servir index.php par défaut

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$file = __DIR__ . $uri;

// Si c'est la racine, servir index.php
if ($uri === '/' || $uri === '' || $uri === '/index.php') {
    if (file_exists(__DIR__ . '/index.php')) {
        require_once __DIR__ . '/index.php';
        return true;
    }
}

// Si le fichier demandé existe et est un fichier, le servir directement
if ($uri !== '/' && file_exists($file) && is_file($file)) {
    return false; 
}

// Si c'est un fichier PHP qui existe (sans extension dans l'URL)
if ($uri !== '/' && file_exists($file . '.php')) {
    require_once $file . '.php';
    return true;
}

$extensions_statiques = ['css', 'js', 'jpg', 'jpeg', 'png', 'gif', 'webp', 'ico', 'svg'];
$extension = strtolower(pathinfo($uri, PATHINFO_EXTENSION));
if (in_array($extension, $extensions_statiques)) {
    return false; // Laisser PHP servir le fichier statique
}

// Sinon, servir index.php
if (file_exists(__DIR__ . '/index.php')) {
    require_once __DIR__ . '/index.php';
    return true;
}

// Si index.php n'existe pas, afficher une erreur
http_response_code(404);
echo "404 - Fichier non trouvé";
return true;

