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

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

$requiredEnvVars = [
    'APP_MAINTENANCE',
    'APP_ENV',
    'DATABASE_HOST',
    'DATABASE_PORT',
    'DATABASE_NAME',
    'DATABASE_USER',
    'DATABASE_PASSWORD'
];

foreach ($requiredEnvVars as $var) {
    if (!isset($_ENV[$var]) && !getenv($var)) {
        die("La variable d'environnement {$var} doit être définie (dans .env ou via Docker)");
    }
    // S'assurer que $_ENV est peuplé si la variable est dans getenv() mais pas $_ENV
    if (!isset($_ENV[$var]) && getenv($var)) {
        $_ENV[$var] = getenv($var);
    }
}

// Auto-migration
try {
    $host = $_ENV['DATABASE_HOST'];
    $port = $_ENV['DATABASE_PORT'];
    $db   = $_ENV['DATABASE_NAME'];
    $user = $_ENV['DATABASE_USER'];
    $pass = $_ENV['DATABASE_PASSWORD'];

    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Vérifier si la table Equipment existe
    $query = $pdo->query("SHOW TABLES LIKE 'Equipment'");
    if ($query->rowCount() === 0) {
        $sql = file_get_contents(__DIR__ . '/../database.sql');
        if ($sql) {
            $pdo->exec($sql);
            
            // Auto-seed après la création des tables
            require_once __DIR__ . '/../seed.php';
            db_seed($pdo);
        }
    }
} catch (PDOException $e) {
    // On ne bloque pas l'application si la migration échoue ici, 
    // sauf si c'est une erreur critique de connexion
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
