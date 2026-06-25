<template>

    <div
        class="print:hidden text-right grid md:grid-cols-3 lg:grid-cols-6 gap-1"
        v-bind="$attrs"
    >

        <template v-for="(filterItem, key) in $page.props.filters">

            <ElFloatingDropdown
                v-if="filterItem.type === 'dropdown' && filterItem.show && (filterItem?.data??[]).length"
                :label="filterItem?.placeholder ? $t(filterItem?.placeholder) : null"
                :option-label="filterItem.optionLabel"
                :form="filter"
                :name="key"
                :options="filterItem.data"
                class="grow max-w-[300px]"
            />

            <ElFloatingTree
                v-if="filterItem.type === 'tree_dropdown' && filterItem.show && (filterItem?.data??[]).length"
                :label="filterItem?.placeholder ? $t(filterItem?.placeholder) : null"
                :option-label="filterItem.optionLabel"
                :form="filter"
                :name="key"
                :options="filterItem.data"
                class="grow max-w-[300px]"
            />

            <ElFloatingMultiSelect
                v-if="filterItem?.type === 'multi_select' && filterItem.show && (filterItem?.data??[]).length"
                :option-label="filterItem.optionLabel"
                :form="filter"
                :name="key"
                :options="filterItem.data"
                class="grow max-w-[300px]"
            />

            <ElFloatingDatePicker
                v-if="filterItem.type === 'date_range' && filterItem.show"
                selectionMode="range"
                :form="filter"
                :name="key"
                class="grow max-w-[300px]"
            />

            <ElFloatingDropdownFromUrl
                :url="filterItem.search_url"
                v-if="filterItem.type === 'search_auto_complete' && filterItem.show"
                :form="filter"
                :name="key" :required="false"
                option-label="name"
                :options="filterItem.data"
                class="grow max-w-[400px]"
            />

        </template>

        <slot/>

    </div>

</template>

<script setup>

import {useQuery} from "@/Helpers/useQuery";
import {ref, watch} from "vue";
import {router, usePage} from "@inertiajs/vue3";
import ElFloatingDropdown from "../Form/ElFloatingDropdown.vue";
import ElFloatingMultiSelect from "../Form/ElFloatingMultiSelect.vue";
import ElFloatingDatePicker from "../Form/ElFloatingDatePicker.vue";
import ElFloatingDropdownFromUrl from "@/Components/Form/ElFloatingDropdownFromUrl.vue";
import ElFloatingTree from "@/Components/Form/ElFloatingTree.vue";

const query = useQuery();

const filter = ref(initFilter(usePage().props.filters));

watch(filter, (data) => {
    let new_filter = {};
    for (const key in data) {
        if (data[key] != null && data[key] !== '') {
            new_filter[key] = data[key];
        }
    }
    const queryParams = new URLSearchParams(window.location.search);
    const searchQuery = queryParams.get('search');

    if (searchQuery) {
        new_filter = {search: searchQuery, ...new_filter};
    }

    const checkDate = checkDateRanges(new_filter);

    if (checkDate)
        return;

    router.get(window.location.pathname, new_filter, {
        preserveState: false,
        preserveScroll: true,
        replace: true,
    })
}, {
    deep: true
})

function initFilter(filters) {
    let queryFilters = {};

    Object.entries(filters).forEach(entry => {
        if (entry[1].type === 'multi_select') {
            const key = entry[0]
            if (entry[1].isInt) {
                queryFilters[key] = query.getArrayInt(key);
            } else {
                queryFilters[key] = query.getArray(key);
            }
        } else if (entry[1].type === 'date_range') {
            const key = entry[0];
            let date_range = [
                query.get(key + '[0]') ? new Date(query.get(key + '[0]')) : null,
                query.get(key + '[1]') ? new Date(query.get(key + '[1]')) : null
            ];
            date_range = date_range.filter(function (el) {
                return el != null;
            });
            queryFilters[key] = date_range.length ? date_range : null;
        } else {
            const [key, filter] = entry;
            queryFilters[key] = query.get(key, filter.isInt);
        }
    });

    return queryFilters;
}

function checkDateRanges(newFilter) {
    const dateRangeKeys = Object.keys(usePage().props.filters).filter(key => usePage().props.filters[key].type === 'date_range');

    for (const key of dateRangeKeys) {
        if (newFilter.hasOwnProperty(key)) {
            const Datevalue = newFilter[key];
            if (Array.isArray(Datevalue) && !Datevalue.every(date => date !== null)) {
                return true;
            }
        }
    }

    return false;
}

</script>
