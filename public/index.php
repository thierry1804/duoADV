<?php

use App\Kernel;

// Protection contre le changement d'environnement via des paramètres de requête
// Supprime APP_ENV et APP_DEBUG des paramètres GET/POST pour éviter les vulnérabilités de sécurité
if (isset($_GET['APP_ENV']) || isset($_POST['APP_ENV'])) {
    unset($_GET['APP_ENV'], $_POST['APP_ENV']);
}
if (isset($_GET['APP_DEBUG']) || isset($_POST['APP_DEBUG'])) {
    unset($_GET['APP_DEBUG'], $_POST['APP_DEBUG']);
}

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return function (array $context) {
    // S'assure que l'environnement ne peut être défini que via les variables d'environnement du serveur
    // et non via des paramètres de requête. Ignore toute valeur dans $context qui pourrait provenir
    // de paramètres de requête manipulés.
    $env = $_ENV['APP_ENV'] ?? $_SERVER['APP_ENV'] ?? $context['APP_ENV'] ?? 'prod';
    $debug = isset($_ENV['APP_DEBUG']) 
        ? filter_var($_ENV['APP_DEBUG'], FILTER_VALIDATE_BOOLEAN)
        : (isset($_SERVER['APP_DEBUG']) 
            ? filter_var($_SERVER['APP_DEBUG'], FILTER_VALIDATE_BOOLEAN)
            : (bool) ($context['APP_DEBUG'] ?? false));
    
    return new Kernel($env, $debug);
};
