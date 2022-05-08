<?php

namespace Degecko\NovaFiltersSummary;

use Laravel\Nova\Card;

class FiltersSummary extends Card
{
    /**
     * @var string
     */
    public $width = 'full';

    /**
     * @var array
     */
    protected $labels = [];

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

    /**
     * @return string
     */
    public function component()
    {
        return 'nova-filters-summary';
    }

    /**
     * @param array $labels
     * @return self
     */
    public function labels(array $labels)
    {
        $this->labels = $labels;

        return $this;
    }

    /**
     * @param false $stacked
     * @return self
     */
    public function stacked($stacked = true)
    {
        return $this->withMeta(compact('stacked'));
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return array_merge([
            'width' => $this->width,
            'labels' => $this->labels,
        ], parent::jsonSerialize());
    }

    
}
