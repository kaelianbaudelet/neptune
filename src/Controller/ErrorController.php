<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\DependencyContainer;
use Twig\Environment;

class ErrorController
{
    private $twig;

    public function __construct(Environment $twig, DependencyContainer $dependencyContainer)
    {
        $this->twig = $twig;
    }

    // Page introuvable
    public function error404()
    {
        echo $this->twig->render('errorController/404.html.twig', []);
    }

    // Permissions non accordées
    public function error403()
    {
        echo $this->twig->render('errorController/403.html.twig', []);
    }

    // Erreur interne du serveur
    public function error500()
    {
        echo $this->twig->render('errorController/500.html.twig', []);
    }
}
