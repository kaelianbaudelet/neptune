<?php

namespace App\Extension;

class UtilsExtension extends \Twig\Extension\AbstractExtension
{
    public function getFunctions()
    {
        return [
            new \Twig\TwigFunction('dump', [$this, 'dump']),
            new \Twig\TwigFunction('vardump', [$this, 'vardump']),
            new \Twig\TwigFunction('env', [$this, 'env']),
            new \Twig\TwigFunction('session', [$this, 'session']),
            new \Twig\TwigFunction('asset', [$this, 'asset']),
            new \Twig\TwigFunction('current_route', [$this, 'currentRoute']),
        ];
    }

    public function dump($var)
    {
        dump($var);
    }

    public function vardump($var)
    {
        echo '<pre>';
        var_dump($var);
        echo '</pre>';
    }

    public function env($key)
    {
        return $_ENV[$key];
    }

    public function session($key)
    {
        return $_SESSION[$key];
    }

    public function asset($asset)
    {
        return sprintf('/assets/%s', ltrim($asset, '/'));
    }

    public function currentRoute()
    {
        global $router;
        return $router->getCurrentRoute();
    }
}
