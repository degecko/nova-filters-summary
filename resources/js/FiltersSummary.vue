<template>
    <div class="filters-summary-card" v-if="activeFilters.length">
        <h3 class="flex mb-3">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 20 20" class="fill-current"><path fill-rule="nonzero" d="M.293 5.707A1 1 0 0 1 0 4.999V1A1 1 0 0 1 1 0h18a1 1 0 0 1 1 1v4a1 1 0 0 1-.293.707L13 12.413v2.585a1 1 0 0 1-.293.708l-4 4c-.63.629-1.707.183-1.707-.708v-6.585L.293 5.707zM2 2v2.585l6.707 6.707a1 1 0 0 1 .293.707v4.585l2-2V12a1 1 0 0 1 .293-.707L18 4.585V2H2z"></path></svg>

            <span class="ml-2">
                {{ activeFilters.length }}
                {{ card.labels.active }}
                {{ activeFilters.length === 1 ? card.labels.filter : card.labels.filters }}
            </span>
        </h3>

        <div class="flex flex-wrap">
            <card class="fsc-filter flex bg-white shadow px-1 py-1 mr-2 mb-2"
                 :class="card.stacked ? 'rounded' : 'rounded-full items-center'"
                 v-for="filter in activeFilters" :key="filter">
                <template v-if="card.stacked">
                    <div class="p-1">
                        <div class="text-sm font-bold mb-1">{{ filter.name }}</div>
                        <div v-html="filter.summary"></div>
                    </div>
                </template>

                <template v-else>
                    <div class="pl-2">{{ filter.name }}:</div>
                    <div class="ml-2 font-bold" v-html="filter.summary"></div>
                </template>

                <div class="ml-2">
                    <BasicButton type="button"
                                 class="remove-filter"
                                 :class="card.stacked ? 'rounded' : 'rounded-full'"
                                 @click="del(filter)">
                        <span>&times;</span>
                    </BasicButton>
                </div>
            </card>
        </div>
    </div>
</template>

<style lang="scss">
.filters-summary-card {
    min-height: 0;

    h3 {
        align-items: center;
        font-size: 1rem;

        span {
            position: relative;
            top: 1px;
        }
    }

    .rounded-full {
        .remove-filter {
            border-radius: 50%;
        }
    }

    .remove-filter {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 22px;
        height: 22px;
        border-radius: 4px;
        transition: all .2s;

        &:hover {
            background-color: rgba(0, 0, 0, .05);
        }

        span {
            position: relative;
            top: -1px;
        }
    }

    .fsc-filter {
        em {
            opacity: .25;
        }
    }
}

.dark {
    .filters-summary-card {
        .remove-filter {
            &:hover {
                background-color: rgba(0, 0, 0, .4);
            }
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
        getFilters () {
            return Nova.store.getters[`${this.resourceName}/filters`]
        },

        getActiveFilters () {
            this.activeFilters = this.getFilters()

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

                .map(f => {
                    const filter = JSON.parse(JSON.stringify(f))
                    
                    if (Nova.filtersSummaryResolvers.hasOwnProperty(filter.component)) {
                        filter.summary = Nova.filtersSummaryResolvers[filter.component](filter)
                    }

                    else if (filter.component === 'select-filter') {
                        const option = filter.options
                            .find(o => this.nin(o.value) === this.nin(filter.currentValue))

                        delete filter.options

                        filter.summary = option.hasOwnProperty('label') ? option.label : option.name
                    }

                    else if (filter.component === 'boolean-filter') {
                        const enabledValues = []
                        const map = []

                        filter.options.map(o => map[this.nin(o.value)] = o.hasOwnProperty('label') ? o.label : o.name)

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
            Nova.store.commit(`${this.resourceName}/updateFilterState`, {
                filterClass: filter.class,
                value: clearValue,
            })

            // Get the active filters excluding the current one.
            this.$nextTick(() => {
                const filters = this.getFilters().map(f => ({ [f.class]: f.currentValue }))

                const qs = (location.search || '')
                    .substr(1)
                    .replace(new RegExp(`${this.resourceName}_page=\d+`),
                            `${this.resourceName}_page=1`)
                    .replace(new RegExp(`${this.resourceName}_filter=[^&]+`),
                            `${this.resourceName}_filter=${encodeURIComponent(btoa(JSON.stringify(filters)))}`)

                history.replaceState(null, null, `${location.origin}${location.pathname}?${qs}`)
            })
        },
    },

    created () {
        this.getActiveFilters()

        this.$watch(() => this.getFilters(), () => this.getActiveFilters(), { deep: true })
    },
}
</script>
