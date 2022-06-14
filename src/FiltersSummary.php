<?php

namespace Degecko\NovaFiltersSummary;

use Laravel\Nova\Card;

class FiltersSummary extends Card
{
    public $component = 'nova-filters-summary';

    public $width = 'full';

    protected array $labels = [];

    /**
     * @param null|string $component
     */
    public function __construct($component = null)
    {
        parent::__construct($component);

        $this->labels = [
            'active' => function_exists('__') ? __('active') : 'active',
            'filter' => function_exists('__') ? __('filter') : 'filter',
            'filters' => function_exists('__') ? __('filters') : 'filters',
        ];
    }

    public function labels(array $labels): static
    {
        $this->labels = $labels;

        return $this;
    }

    public function stacked($stacked = true): static
    {
        return $this->withMeta(compact('stacked'));
    }

    public function jsonSerialize(): array
    {
        return array_merge([
            'width' => $this->width,
            'labels' => $this->labels,
        ], parent::jsonSerialize());
    }
}
