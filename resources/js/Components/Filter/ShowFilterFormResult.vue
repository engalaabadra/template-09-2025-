<template>

    <div
        v-if="queryFilters.length"
        class="print:hidden text-right flex flex-wrap filter-elements grow gap-2"
        v-bind="$attrs"
    >

        <section
            v-for="filterItem in queryFilters"
            :key="Math.random() + 'filter'"
        >

            <aside
                v-if="filterItem.type==='multi_select' || filterItem.type === 'dropdown' || filterItem.type === 'tree_dropdown' || filterItem.type === 'date_range' || filterItem.type === 'search_auto_complete'"
            >

                <div class="bg-primary-500 text-white p-2 rounded-lg flex items-center gap-2">

                    {{ filterItem.label }} :

                    <div v-if="filterItem.type==='multi_select'" v-for="(nameToShow, index) in filterItem.name">
                        <span class="me-2" v-if="index !== Object.keys(filterItem.name).length-1">-</span>
                        {{ nameToShow }}
                    </div>

                    <div
                        v-else-if="typeof(filterItem.el_values) == 'object'"
                        v-for="(item,index) in filterItem.el_values"
                    >
                        <span class="me-2" v-if="index !== Object.keys(filterItem.el_values).length-1">-</span>
                        {{ item }}
                    </div>

                    <div v-else>{{ filterItem.name ?? filterItem.el_values }}</div>

                    <i
                        class="pi pi-times-circle cursor-pointer"
                        @click="removeFilter(filterItem)"
                    />

                </div>

            </aside>

        </section>

    </div>

</template>

<script setup>

import {useQuery} from "@/Helpers/useQuery";
import {router, usePage} from "@inertiajs/vue3";
import {useI18n} from "vue-i18n";
import {ref, watch} from "vue";

const query = useQuery();

const {t} = useI18n()

const emit = defineEmits(['filterChange']);

const queryFilters = ref([]);

const renderFilterObject = () => {
    queryFilters.value = [];
    let shared_filters = usePage().props.filters;
    for (const argumentsKey in shared_filters) {
        let item = shared_filters[argumentsKey];
        if (item.type === 'multi_select') {
            let names = [];
            if (item.isInt) {
                let el_val = query.getArrayInt(argumentsKey);
                if (el_val?.length) {
                    item.data.forEach(itemt => {
                        if (el_val.includes(itemt.id)) {
                            names.push(itemt[item.optionLabel])
                        }
                    })
                    queryFilters.value.push({
                        'el_values': el_val,
                        'label': t('column.' + argumentsKey ?? argumentsKey),
                        'key': argumentsKey,
                        'type': item.type,
                        'name': names
                    });
                }
            } else {
                let el_val = query.getArray(argumentsKey);
                item.data.forEach(itemt => {
                    if (el_val.includes(String(itemt.id))) {
                        names.push(itemt[item.optionLabel])
                    }
                })
                if (el_val.length)
                    queryFilters.value.push({
                        'el_values': el_val,
                        'label': t('column.' + argumentsKey ?? argumentsKey),
                        'key': argumentsKey,
                        'type': item.type,
                        'name': names
                    });
            }

            continue;
        }

        if (item.type === 'date_range') {
            let date_range = [
                query.get(argumentsKey + '[0]') ? formatDate(query.get(argumentsKey + '[0]')) : null,
                query.get(argumentsKey + '[1]') ? formatDate(query.get(argumentsKey + '[1]')) : null
            ];
            date_range = date_range.filter(function (el) {
                return el != null;
            });

            if (date_range.length)
                queryFilters.value.push({
                    'el_values': date_range.length ? date_range.join(' - ') : null,
                    'label': t('column.' + argumentsKey ?? argumentsKey),
                    'key': argumentsKey,
                    'type': item.type
                });

            continue;
        }

        if (item.type === 'dropdown') {
            // const [argumentsKey, filter] = item;
            const filter = item;
            if (query.get(argumentsKey, filter.isInt))
                queryFilters.value.push({
                    'el_values': query.get(argumentsKey, filter.isInt),
                    'label': t('column.' + argumentsKey ?? argumentsKey),
                    'key': argumentsKey,
                    'type': item.type,
                    'name': Array.from(filter.data).find(dataum => dataum.id == query.get(argumentsKey, filter.isInt))?.[item.optionLabel]
                });


        }

        if (item.type === 'tree_dropdown') {
            const filter = item;
            if (query.get(argumentsKey, filter.isInt))
                queryFilters.value.push({
                    'el_values': query.get(argumentsKey, filter.isInt),
                    'label': t('column.' + argumentsKey ?? argumentsKey),
                    'key': argumentsKey,
                    'type': item.type,
                    'name': findItemByIdOnTree(Array.from(filter.data), query.get(argumentsKey, filter.isInt))?.label
                });


        }

        if (item.type === 'search_auto_complete') {
            const filter = item;
            if (query.get(argumentsKey, filter.isInt)) {
                queryFilters.value.push({
                    'el_values': query.get(argumentsKey, filter.isInt),
                    'label': t('column.' + argumentsKey ?? argumentsKey),
                    'key': argumentsKey,
                    'type': item.type,
                    'name': Array.from(filter.data).find(dataum => dataum.id == query.get(argumentsKey, filter.isInt))?.name
                });
            }
        }
    }
}

function findItemByIdOnTree(dataArray, targetId) {
    for (const item of dataArray) {
        if (item.children && item.children.length) {
            for (const child of item.children) {
                if (child.id === targetId) {
                    return child;
                }
            }
        }
    }
    return null;
}

function formatDate(dateValue) {
    let date = new Date(dateValue);
    const offset = date.getTimezoneOffset()
    date = new Date(date.getTime() - (offset * 60 * 1000))
    return date.toISOString().split('T')[0]
}

renderFilterObject();
watch(() => usePage().props.filters, function (val) {
    renderFilterObject();
    emit('filterChange', queryFilters.value.length);
}, {
    immediate: true,
    deep: true,
});

const removeFilter = (filter_item) => {
    const queryParams = new URLSearchParams(window.location.search);
    if (filter_item.type === 'multi_select') {
        let arr = filter_item.el_values;
        arr.forEach((element, i) => {
            queryParams.delete(filter_item.key + `[${i}]`);
        });
    } else if (filter_item.type === 'date_range') {
        queryParams.delete(filter_item.key + '[0]');
        queryParams.delete(filter_item.key + '[1]');
    } else {
        queryParams.delete(filter_item.key);
    }
    const newUrl = window.location.pathname + '?' + queryParams.toString();

    router.get(newUrl, {}, {
        preserveState: false,
        preserveScroll: true,
        replace: true,
    })

    renderFilterObject();
}

</script>

<style>

.filter-elements .p-dropdown-clear-icon {
    margin-right: 0px !important;
}

</style>

