<?php

namespace App\Services;

class MontantEnLettres
{
    public function francs(float $montant): string
    {
        $centimes = (int) round($montant * 100);
        $entier = intdiv($centimes, 100);

        return $this->nombre($entier).' francs CFA'.($centimes % 100 ? ' et '.$this->nombre($centimes % 100).' centimes' : '');
    }

    private function nombre(int $n): string
    {
        if (class_exists(\NumberFormatter::class)) {
            return (new \NumberFormatter('fr', \NumberFormatter::SPELLOUT))->format($n);
        }
        $unites = ['zero', 'un', 'deux', 'trois', 'quatre', 'cinq', 'six', 'sept', 'huit', 'neuf', 'dix', 'onze', 'douze', 'treize', 'quatorze', 'quinze', 'seize'];
        if ($n < 17) { return $unites[$n]; }
        if ($n < 20) { return 'dix-'.$unites[$n - 10]; }
        if ($n < 70) {
            $dizaines = [2 => 'vingt', 3 => 'trente', 4 => 'quarante', 5 => 'cinquante', 6 => 'soixante'];
            return $dizaines[intdiv($n, 10)].($n % 10 === 0 ? '' : ($n % 10 === 1 ? ' et un' : '-'.$unites[$n % 10]));
        }
        if ($n < 80) { return 'soixante'.($n === 71 ? ' et ' : '-').$this->nombre($n - 60); }
        if ($n < 100) { return $n === 80 ? 'quatre-vingts' : 'quatre-vingt-'.$this->nombre($n - 80); }
        if ($n < 1000) {
            return (intdiv($n, 100) === 1 ? 'cent' : $unites[intdiv($n, 100)].' cent'.($n % 100 === 0 ? 's' : '')).
                ($n % 100 ? ' '.$this->nombre($n % 100) : '');
        }
        foreach ([1000000000000 => 'billion', 1000000000 => 'milliard', 1000000 => 'million', 1000 => 'mille'] as $base => $nom) {
            if ($n >= $base) {
                $q = intdiv($n, $base);
                $prefixe = $base === 1000 && $q === 1 ? '' : $this->nombre($q).' ';
                if ($base === 1000) { $prefixe = preg_replace('/(cent|vingt)s $/', '$1 ', $prefixe) ?? $prefixe; }
                return $prefixe.$nom.($base !== 1000 && $q > 1 ? 's' : '').($n % $base ? ' '.$this->nombre($n % $base) : '');
            }
        }

        return (string) $n;
    }
}
