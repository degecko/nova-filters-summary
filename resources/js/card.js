Nova.filtersSummaryResolvers = {}

Nova.booting((Vue, router, store) => {
    Vue.component('nova-filters-summary', require('./FiltersSummary').default)
})
