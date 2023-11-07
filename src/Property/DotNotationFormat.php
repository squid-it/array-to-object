<?php

declare(strict_types=1);

namespace SquidIT\Hydrator\Property;

enum DotNotationFormat: string
{
    case JAVASCRIPT = 'javascript';
    case EXPLODE    = 'explode';
}
