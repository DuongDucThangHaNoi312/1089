<?php

namespace App\Twig;

use App\Service\AbbreviateState;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class StateAbbreviationExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('abbreviate', [$this, 'abbreviate']),
        ];
    }

    public function abbreviate($state) {
        return AbbreviateState::format_state($state, "abbr");
    }
}