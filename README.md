# Laravel Nova Filters Summary

A custom Card component for [Laravel Nova](https://nova.laravel.com/) which displays a summary of the active filters for a specific resource or lens.

Default Preview:

![](https://i.imgur.com/zGqRgIZ.png)

Stacked Preview:

![](https://i.imgur.com/PYEPNaA.png)

---

## Installation

Using [Composer](https://getcomposer.org/):

```
composer require degecko/nova-filters-summary
```

## Usage

Add it to the array returned by the `cards` method inside a Nova Resource or Lens.

```
<?php

namespace App\Nova;

use Degecko\NovaFiltersSummary\FiltersSummary;
use Illuminate\Http\Request;

class Product extends Resource
{
    // Your setup.
    
    public function cards(Request $request)
    {
        return [
            FiltersSummary::make(),
        ];
    }
}
```

### Stacked

To use the stacked template, use `FiltersSummary::make()->stacked()`.

---

## Custom Filter Summary Resolvers

The plugin knows how to display the information of the default Nova filters, but you might need to use custom filters. In that case, you'll probably need to create a custom template for the filter summary.

E.g.: I had a custom filter that allows a user to pick a numeric range of a specific column. You could use it to select products that have prices between 5 and 20 USD.

Now, because the filter maps to an object like this:

`{ from: 5, to: 20 }`

The summary would simply list that as its value, which isn't very pretty.

Therefore, if you want to customize that, you need to create a custom summary resolver for that filter.

The setup is pretty straight forward:

- Define a function inside `Nova.filtersSummaryResolvers` using the same name as your filter component
- The function will be fed the filter itself as a parameter
- Return the filter summary

I usually create the definition inside the filter's `index.js` file and it looks like this:

```
Nova.booting((Vue, router, store) => {
    Vue.component('numeric-range-filter', require('./NumericFilter').default)

    Nova.filtersSummaryResolvers['numeric-range-filter'] = filter => {
        return [filter.currentValue.from || '<em>•</em>', filter.currentValue.to || '<em>•</em>'].join(' — ')
    }
})
```
