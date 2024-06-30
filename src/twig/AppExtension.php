<?php

// src/Twig/AppExtension.php

namespace App\twig;

use App\Service\PanierNotif;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    private $panierNotif;

    public function __construct(PanierNotif $panierNotif)
    {
        $this->panierNotif = $panierNotif;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('nombreInPanier', [$this, 'nombreInPanier']),
        ];
    }

    public function nombreInPanier(): int
    {
        return $this->panierNotif->cartNotif();
    }
}
