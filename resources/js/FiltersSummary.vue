<template>
    <div class="filters-summary-card" v-if="activeFilters.length">
        <h3 class="flex align-items-center mb-3 font-normal">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 20 20" class="fill-current"><path fill-rule="nonzero" d="M.293 5.707A1 1 0 0 1 0 4.999V1A1 1 0 0 1 1 0h18a1 1 0 0 1 1 1v4a1 1 0 0 1-.293.707L13 12.413v2.585a1 1 0 0 1-.293.708l-4 4c-.63.629-1.707.183-1.707-.708v-6.585L.293 5.707zM2 2v2.585l6.707 6.707a1 1 0 0 1 .293.707v4.585l2-2V12a1 1 0 0 1 .293-.707L18 4.585V2H2z"></path></svg>

            <span class="ml-2">
                {{ activeFilters.length }}
                {{ card.labels.active }}
                {{ activeFilters.length === 1 ? card.labels.filter : card.labels.filters }}
            </span>
        </h3>

        <div class="flex flex-wrap">
            <div class="fsc-filter flex bg-white shadow px-1 py-1 mr-2 mb-2"
                 :class="card.stacked ? 'rounded' : 'rounded-full align-items-center'"
                 v-for="filter in activeFilters" :key="filter" >
                <template v-if="card.stacked">
                    <div class="p-1">
                        <div class="text-sm font-bold mb-1">{{ filter.name }}</div>
                        <div v-html="filter.currentValue"></div>
                    </div>
                </template>

                <template v-else>
                    <div class="pl-2">{{ filter.name }}:</div>
                    <div class="ml-2 font-bold" v-html="filter.currentValue"></div>
                </template>

                <div class="ml-2">
                    <div class="remove-filter flex align-items-center justify-center font-bold bg-40 cursor-pointer hover:text-white hover:bg-90" :class="card.stacked ? 'rounded' : 'rounded-full'" @click="del(filter)">&times;</div>
                </div>
            </div>
        </div>
    </div>
</template>

<style lang="scss">
.filters-summary-card {
    h3 {
        span {
            position: relative;
            top: 1px;
        }
    }

    .remove-filter {
        width: 22px;
        height: 22px;
    }

    .fsc-filter {
        em {
            opacity: .25;
        }
    }
}
</style>

<script>
export default {
    props: {
        card: {},

        resourceName: {
            type: String,
            required: true,
        },
    },

    data () {
        return {
            activeFilters: [],
        }
    },


    methods: {
        watchForFilterChanges () {
            this.$watch(
                () => this.$store.getters[`${this.resourceName}/filters`],
                this.getActiveFilters,
                { deep: true }
            )
        },

        getActiveFilters (filters = this.$store.getters[`${this.resourceName}/filters`]) {
            this.activeFilters = _.cloneDeep(filters)

                .filter(filter => {
                    if (filter.component === 'boolean-filter') {
                        if (typeof filter.currentValue === 'object') {
                            for (let id in filter.currentValue) {
                                if (filter.currentValue.hasOwnProperty(id) && filter.currentValue[id]) {
                                    return true
                                }
                            }
                        }

                        return false
                    }

                    return !! filter.currentValue
                })

                .map(filter => {
                    if (Nova.filtersSummaryResolvers[filter.component]) {
                        filter.summary = Nova.filtersSummaryResolvers[filter.component](filter)
                    }

                    else if (filter.component === 'select-filter') {
                        filter.summary = filter.options
                            .find(o => this.nin(o.value) === this.nin(filter.currentValue))?.name
                    }

                    else if (filter.component === 'boolean-filter') {
                        const enabledValues = []
                        const map = []

                        filter.options.map(o => map[this.nin(o.value)] = o.name)

                        for (let id in filter.currentValue) {
                            if (filter.currentValue[id]) {
                                enabledValues.push(map[id])
                            }
                        }

                        filter.summary = enabledValues.length ? enabledValues.join(', ') : '<em>none</em>'
                    }

                    else filter.summary = filter.currentValue || 'N/A'
                    
                    return filter
                })
        },

        nin (maybeNumber) {
            return isNaN(maybeNumber) ? maybeNumber : Number(maybeNumber)
        },

        del (filter) {
            let clearValue = ''

            if (filter.component === 'boolean-filter') {
                Object.keys(filter.currentValue).map(key => {
                    filter.currentValue[key] = false
                })

                clearValue = filter.currentValue
            }

            // Reset the filter's value.
            this.$store.commit(`${this.resourceName}/updateFilterState`, {
                filterClass: filter.class,
                value: clearValue,
            })

            // Get the active filters excluding the current one.
            this.$nextTick(() => {
                const activeFilters = this.activeFilters
                    .filter(f => f.class !== filter.class)
                    .map(f => ({ class: f.class, value: f.currentValue }))

                // Remove the current filter from the URL.
                // this.$router.push({
                //     query: _.defaults({
                //         [`${this.resourceName}_page`]: 1,
                //         [`${this.resourceName}_filter`]: btoa(JSON.stringify(activeFilters)),
                //     }, this.$root.$route.query)
                // })
            })
        },
    },
    
    created () {
        this.getActiveFilters()
        this.watchForFilterChanges()
    },

    updated() {
        return this.$el.classList.remove('min-h-40');
    }
}
</script>
