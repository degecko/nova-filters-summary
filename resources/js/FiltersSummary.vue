<template>
    <div class="filters-summary-card" v-if="activeFilters.length">
        <h3 class="flex align-items-center mb-3 font-normal">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 20 20" class="fill-current"><path fill-rule="nonzero" d="M.293 5.707A1 1 0 0 1 0 4.999V1A1 1 0 0 1 1 0h18a1 1 0 0 1 1 1v4a1 1 0 0 1-.293.707L13 12.413v2.585a1 1 0 0 1-.293.708l-4 4c-.63.629-1.707.183-1.707-.708v-6.585L.293 5.707zM2 2v2.585l6.707 6.707a1 1 0 0 1 .293.707v4.585l2-2V12a1 1 0 0 1 .293-.707L18 4.585V2H2z"></path></svg>

            <span class="ml-2">
                {{ activeFilters.length }}
                {{ __('active') }}
                {{ __(activeFilters.length === 1 ? 'filter' : 'filters') }}
            </span>
        </h3>

        <div class="flex flex-wrap">
            <div class="flex align-items-center bg-white shadow px-1 py-1 rounded rounded-full mr-2 mb-2"
                 v-for="filter in activeFilters">
                <div class="pl-2">{{ filter.name }}:</div>

                <div class="ml-2 font-bold">{{ filter.summary || filter.currentValue || 'N/A' }}</div>

                <div class="ml-2">
                    <div class="remove-filter flex align-items-center justify-center font-bold bg-40 rounded rounded-full cursor-pointer hover:text-white hover:bg-90" @click="del(filter)">&times;</div>
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
        width: 24px;
        height: 24px;
    }
}
</style>

<script>
export default {
    props: {
        resourceName: {
            type: String,
            required: true,
        },
    },

    computed: {
        activeFilters () {
            return _.cloneDeep(this.$store.getters[`${this.resourceName}/filters`])
                .filter(filter => !! filter.currentValue)
                .map(filter => {
                    if (filter.component === 'select-filter') {
                        filter.summary = filter.options.find(o => o.value == filter.currentValue)?.name
                    } else if (filter.component === 'date-range-filter') {
                        if (filter.currentValue.from && filter.currentValue.to) {
                            const from = filter.currentValue.from.replace(/(\d{4})-(\d\d)-(\d\d)/, '$3.$2.$1')
                            const to = filter.currentValue.to.replace(/(\d{4})-(\d\d)-(\d\d)/, '$3.$2.$1')

                            filter.summary = [from, this.__('To'), to].join(' ')
                        } else if (filter.currentValue.from) {
                            const from = filter.currentValue.from.replace(/(\d{4})-(\d\d)-(\d\d)/, '$3.$2.$1')

                            filter.summary = `Après ${from}`
                        } else if (filter.currentValue.to) {
                            const to = filter.currentValue.to.replace(/(\d{4})-(\d\d)-(\d\d)/, '$3.$2.$1')

                            filter.summary = `jusqu'à ${to}`
                        }
                    } else if (filter.component === 'numeric-range-filter') {
                        filter.summary = [filter.currentValue.from, filter.currentValue.to].join(' — ')
                    }

                    return filter
                })
        },
    },

    methods: {
        del (filter) {
            this.$store.commit(`${this.resourceName}/updateFilterState`, {
                filterClass: filter.class,
                value: '',
            })

            const activeFilters = this.activeFilters.filter(f => f.class !== filter.class).map(f => ({
                class: f.class,
                value: f.currentValue,
            }))

            this.$router.push({
                query: _.defaults({
                    [`${this.resourceName}_page`]: 1,
                    [`${this.resourceName}_filter`]: btoa(JSON.stringify(activeFilters)),
                }, this.$root.$route.query)
            })
        },
    },
}
</script>
