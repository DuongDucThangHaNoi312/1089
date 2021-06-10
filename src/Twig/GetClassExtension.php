<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class GetClassExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('get_class', [$this, 'getClass']),
        ];
    }

    public function getClass($value)
    {
        $class = '';

        if (is_object($value)) {
            $class = get_class($value);
        }

        return $class;
    }
}
