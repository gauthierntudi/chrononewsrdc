<?php

namespace App\Enums;

enum ArticleCategory: string
{
    case News = 'Actualités';
    case Institutions = 'Institutions';
    case Politics = 'Politique';
    case Economy = 'Économie';
    case JusticeSecurity = 'Justice & Sécurité';
    case DevelopmentInfra = 'Développement & Infrastructures';
    case Society = 'Société';
    case International = 'International';
    case Sport = 'Sport';
    case Interviews = 'Interviews';
    case Decryptage = 'Décryptage';

    public function color(): string
    {
        return match ($this) {
            self::News => '#d11810',
            self::Institutions => '#1E5EFF',
            self::Politics => '#e6a406',
            self::Economy => '#ce5105',
            self::JusticeSecurity => '#434547',
            self::DevelopmentInfra => '#09b960',
            self::Society => '#6709dc',
            self::International => '#0457d3',
            self::Sport => '#059669',
            self::Interviews => '#2b3a6c',
            self::Decryptage => '#626a6b',
        };
    }

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
