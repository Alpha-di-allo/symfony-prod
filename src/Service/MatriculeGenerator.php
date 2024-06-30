<?php

// src/Service/MatriculeGenerator.php

namespace App\Service;

class MatriculeGenerator
{
    public function generateMatricule(): string
    {
        // definition de mes variable lettre de A a Z  et chiffre de 0 a 9 
        $letters = range('A', 'Z');
        $digits = range(0, 9);
        
        // matricule mis a vide 
        $matricule = '';
        
        // Générer deux lettres aléatoires en le concaténant avec mon matricule a vide 
        $matricule .= $letters[array_rand($letters)];
        $matricule .= $letters[array_rand($letters)];
        
        // Générer trois chiffres aléatoires à ala suite de mes 2 premiere lettres en concaténant toujours 
        for ($i = 0; $i < 3; $i++) {
            $matricule .= $digits[array_rand($digits)];
        }
        
        return $matricule;
    }
}
