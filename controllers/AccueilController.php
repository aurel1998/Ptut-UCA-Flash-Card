<?php
require_once __DIR__ . '/../config.php';

class AccueilController {
    public function index() {
        require_once __DIR__ . '/../views/layouts/header.php';
        require_once __DIR__ . '/../views/accueil.php';
        require_once __DIR__ . '/../views/layouts/footer.php';
    }
}

