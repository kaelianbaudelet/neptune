<?php

namespace App\Extension;

class SessionExtension extends \Twig\Extension\AbstractExtension
{
    public function getFunctions()
    {
        return [
            new \Twig\TwigFunction('clear_session_alert', [$this, 'clearSessionAlert']),
        ];
    }

    public function clearSessionAlert()
    {
        $_SESSION['alert'] = null;
    }
}
