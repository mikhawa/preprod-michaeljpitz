<?php

declare(strict_types=1);

namespace App\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('Breadcrumb')]
class BreadcrumbComponent
{
    /** @var list<array{label: string, url?: string}> */
    public array $items = [];
}
