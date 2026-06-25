<template>

    <!-- <div
        v-if="usePage().props.allowSearch || usePage().props.filters"
        class="flex items-center justify-start space-x-4 w-full"
    >

        <IconField v-if="usePage().props.allowSearch">

            <InputIcon :class="!processing ? 'pi pi-search' : 'pi pi-spin pi-spinner'"/>

            <InputText
                v-model="search"
                :placeholder="$t('message.search_here')"
                size="small"
            />

        </IconField>

        <Button v-if="usePage().props.filters"
            @click="toggleFilter"
            outlined
            class="overflow-visible!"
        >

            <i class="pi pi-filter-fill"/>

            <Badge
                :value="numOfFilters"
                severity="primary"
                class="absolute -top-2 -left-2 text-xs"
            />

        </Button>

    </div>

    <ShowFilterFormResult @filterChange="filterChange"/>

    <div v-if="showFilterContent" class="p-5 border rounded-lg shadow">
        <FilterForm/>
    </div> -->

</template>

<script setup>

import {ref, watch} from "vue";
import {refDebounced} from "@vueuse/core";
import {router, usePage} from "@inertiajs/vue3";
import {useQuery} from "@/Helpers/useQuery.js";
import FilterForm from "@/Components/Filter/FilterForm.vue";
import ShowFilterFormResult from "@/Components/Filter/ShowFilterFormResult.vue";
// import {IconField, InputIcon, Badge, InputText, Button} from 'primevue';
import {isNotNull} from "@/Helpers/Functions.js";

const search = ref(useQuery().get('search'));

const processing = ref(false);

const searchD = refDebounced(search, 500);

const showFilterContent = ref(false);

const numOfFilters = ref(0);

const filterChange = (filter) => {
    numOfFilters.value = filter ?? 0
}

const toggleFilter = () => {
    showFilterContent.value = !showFilterContent.value;
};

watch(searchD, (search) => {
    processing.value = true;
    router.reload({
        data: {search},
        onFinish: () => {
            processing.value = false;
        },
    })
});

</script>
