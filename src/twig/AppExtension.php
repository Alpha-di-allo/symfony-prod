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

    public function getFunctions(): array    // déclare la nouvelle fonction Twig nombreInPanier 
    {
        return [
            new TwigFunction('nombreInPanier', [$this, 'nombreInPanier']),
        ];
    }

    public function nombreInPanier(): int  // la méthode appelle le service pour obtenir le nombre d'article dans le panier 
    {
        return $this->panierNotif->cartNotif();
    }
}
