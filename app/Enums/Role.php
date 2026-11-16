<?php

namespace App\Enums;

// Evdeki roller. Veritabanında string olarak durur, kodda enum olarak kullanılır.
enum Role: string
{
    case Eltern = 'eltern';
    case Kind = 'kind';
    case Gast = 'gast';

    // Ekranda görünen Almanca ad.
    public function label(): string
    {
        return match ($this) {
            self::Eltern => __('Eltern'),
            self::Kind => __('Kind'),
            self::Gast => __('Gast'),
        };
    }
}
