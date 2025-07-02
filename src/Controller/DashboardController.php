<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\DependencyContainer;
use Twig\Environment;

class DashboardController
{
    private $twig;

    public function __construct(Environment $twig, DependencyContainer $dependencyContainer)
    {
        $this->twig = $twig;
    }

    public function dashboard()
    {
        echo $this->twig->render('dashboardController/dashboard.html.twig', []);
    }
}
