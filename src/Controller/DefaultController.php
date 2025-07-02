<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\DependencyContainer;
use Twig\Environment;

// Le controleur par defaut est en charge des pages générales du site

class DefaultController
{
    private $twig;

    public function __construct(Environment $twig, DependencyContainer $dependencyContainer)
    {
        $this->twig = $twig;
    }

    public function home()
    {

        $arrivalDate = filter_input(INPUT_GET, 'arrival_date', FILTER_SANITIZE_SPECIAL_CHARS) ?? '';
        $departureDate = filter_input(INPUT_GET, 'departure_date', FILTER_SANITIZE_SPECIAL_CHARS) ?? '';
        $numberOfGuests = filter_input(INPUT_GET, 'number_of_guests', FILTER_SANITIZE_NUMBER_INT) ?? '';

        echo $this->twig->render('defaultController/home.html.twig', [
            'arrival_date' => $arrivalDate,
            'departure_date' => $departureDate,
            'number_of_guests' => $numberOfGuests,
        ]);
    }

    public function about()
    {
        echo $this->twig->render('defaultController/about.html.twig', []);
    }

    public function legals()
    {
        echo $this->twig->render('defaultController/legals.html.twig', []);
    }
}
