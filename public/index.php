<?php

declare(strict_types=1);

// public/index.php

/*

                                                        ÆÆ
                                                       ÆÆÆ
                                                       ÆÆÆÆ
                                               Æ      ÆÆÆÆÆÆ     ÆÆ
                                               ÆÆÆÆ   ÆÆÆÆ Æ  ÆÆÆÆ
                                                ÆÆÆÆ   ÆÆÆ   ÆÆÆÆÆ
                                                ÆÆÆÆ   ÆÆÆÆ  ÆÆÆÆ
                                                  ÆÆÆ  ÆÆÆÆ  ÆÆ
                                                  ÆÆÆÆÆÆÆÆÆ ÆÆÆ
                                                  ÆÆÆÆÆÆÆÆÆÆÆÆÆ
                                                  ÆÆÆÆÆÆÆÆÆÆÆÆÆÆ
  ÆÆÆÆ     ÆÆÆÆ     ÆÆÆÆÆÆÆÆÆÆ     ÆÆÆÆÆÆÆÆÆÆ          ÆÆÆÆ        ÆÆÆÆ    ÆÆÆÆ     ÆÆÆÆÆ    ÆÆÆÆÆ    ÆÆÆÆÆÆÆÆÆÆÆ
  ÆÆÆÆÆÆ   ÆÆÆÆ     ÆÆÆÆÆÆÆÆÆÆ     ÆÆÆÆÆÆÆÆÆÆÆ         ÆÆÆÆ        ÆÆÆÆ    ÆÆÆÆ     ÆÆÆÆÆÆ   ÆÆÆÆÆ    ÆÆÆÆÆÆÆÆÆÆÆ
  ÆÆÆÆÆÆÆ  ÆÆÆÆ     ÆÆÆÆ           ÆÆÆÆ   ÆÆÆÆ         ÆÆÆÆ        ÆÆÆÆ    ÆÆÆÆ     ÆÆÆÆÆÆÆÆ ÆÆÆÆÆ    ÆÆÆÆ
  ÆÆÆÆÆÆÆÆÆÆÆÆÆ     ÆÆÆÆÆÆÆÆÆ      ÆÆÆÆÆÆÆÆÆÆÆ         ÆÆÆÆ        ÆÆÆÆ    ÆÆÆÆ     ÆÆÆÆÆÆÆÆÆÆÆÆÆÆ    ÆÆÆÆÆÆÆÆÆÆ
  ÆÆÆÆ ÆÆÆÆÆÆÆÆ     ÆÆÆÆÆÆÆÆÆ      ÆÆÆÆÆÆÆÆÆÆ          ÆÆÆÆ        ÆÆÆÆ    ÆÆÆÆ     ÆÆÆÆ ÆÆÆÆÆÆÆÆÆ    ÆÆÆÆÆÆÆÆÆÆ
  ÆÆÆÆ  ÆÆÆÆÆÆÆ     ÆÆÆÆ           ÆÆÆÆÆÆ              ÆÆÆÆ        ÆÆÆÆÆ  ÆÆÆÆÆ     ÆÆÆÆ  ÆÆÆÆÆÆÆÆ    ÆÆÆÆ
  ÆÆÆÆ   ÆÆÆÆÆÆ     ÆÆÆÆÆÆÆÆÆÆÆ    ÆÆÆÆ                ÆÆÆÆ        ÆÆÆÆÆÆÆÆÆÆÆÆ     ÆÆÆÆ    ÆÆÆÆÆÆ    ÆÆÆÆÆÆÆÆÆÆÆ
  ÆÆÆÆ     ÆÆÆÆ     ÆÆÆÆÆÆÆÆÆÆÆ    ÆÆÆÆ                ÆÆÆÆ          ÆÆÆÆÆÆÆÆ       ÆÆÆÆ     ÆÆÆÆ     ÆÆÆÆÆÆÆÆÆÆÆ

                                              Kaelian BAUDELET © 2025

                    Site d'hôtel open-source, construit avec PHP, PHPMailer, Twig, TailwindCSS.

*/

// Configuration de l'environnement
// ini_set('post_max_size', '10M');
// ini_set('upload_max_filesize', '10M');

// Démarrage de la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

use App\Routing\Router;

use App\Extension\UtilsExtension;
use App\Extension\SessionExtension;

use App\Service\DependencyContainer;

require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\ErrorHandler\Debug;
use Symfony\Component\ErrorHandler\ErrorHandler;
use Symfony\Component\ErrorHandler\DebugClassLoader;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../', '.env');
$dotenv->load();

if (!file_exists(__DIR__ . '/../.env')) {
    die('Le fichier .env est manquant');
}

if (!isset($_ENV['APP_MAINTENANCE'])) {
    die('La variable APP_MAINTENANCE doit être définie dans le fichier .env');
}

if (!isset($_ENV['APP_ENV'])) {
    die('La variable APP_ENV doit être définie dans le fichier .env');
}

if ($_ENV['APP_ENV'] === 'dev') {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
    Debug::enable();
    ErrorHandler::register();
    DebugClassLoader::enable();
}

$container = new DependencyContainer();
$loader = new FilesystemLoader(__DIR__ . '/../views');
$twig = new Environment($loader);
$twig->addGlobal('session', $_SESSION);

$router = new Router($container);

$twig->addGlobal('env', $_ENV);

$twig->addExtension(new SessionExtension());
$twig->addExtension(new UtilsExtension());

if ($_ENV['APP_MAINTENANCE'] === 'true') {
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if (!preg_match('#^/dashboard(/.*)?$#', $path) && $path !== '/login' && $path !== '/resetpassword' && $path !== '/logout') {
        echo $twig->render('maintenance.html.twig');
        exit;
    }
}

$router->route($twig);
