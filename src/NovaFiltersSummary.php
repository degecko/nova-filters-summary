<?php

namespace Degecko\NovaFiltersSummary;

use Laravel\Nova\Card;

class NovaFiltersSummary extends Card
{
    /**
     * @var string
     */
    public $width = 'full';

    /**
     * @return string
     */
    public function component()
    {
        return 'nova-filters-summary';
    }
}
