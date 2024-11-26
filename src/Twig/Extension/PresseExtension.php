<?php

namespace AcMarche\Presse\Twig\Extension;

use AcMarche\Presse\Twig\Runtime\PresseExtensionRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class PresseExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            // If your filter generates SAFE HTML, you should add a third
            // parameter: ['is_safe' => ['html']]
            // Reference: https://twig.symfony.com/doc/3.x/advanced.html#automatic-escaping
            new TwigFilter('article_path', [PresseExtensionRuntime::class, 'articlePath']),
        ];
    }
}
