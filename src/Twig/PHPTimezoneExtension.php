<?php

namespace App\Twig;

use App\Service\AbbreviateState;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class PHPTimezoneExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('phpTimezone', [$this, 'phpTimezone']),
        ];
    }

    public function phpTimezone(?string $timezone) {
        return timezone_name_from_abbr($timezone ?? 'UTC');
    }
}