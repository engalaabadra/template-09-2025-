<template>

    <main
        class="p-2 grid grild-cols-1 gap-4"
        :class="{ 'p-0! gap-2!': isProfilePage }"
        :key="'el_panel_key' + (usePage().props?.other_data?.refresh_dom_key ?? '')"
    >

        <header class="flex justify-between items-center gap-5">

            <div class="flex justify-center items-center gap-2">

                <ElBreadcrumb class="dark:text-white" v-if="showBreadcrumb"/>

                <slot name="header"/>

            </div>

            <div class="flex justify-center items-center gap-2">

                <slot name="actions"/>

            </div>

        </header>

        <div
            class="grid grid-cols-1 gap-5"
            :class="{
                'bg-white border rounded-lg shadow p-5': !usePage().props.isTransparent || isProfilePage,
            }"
        >

            <template v-if="showFilters">
                <ElSearchWithFilter/>
            </template>

            <slot/>

        </div>

    </main>

</template>

<script setup>

import ElBreadcrumb from "@/Components/Main/ElBreadcrumb.vue";
import ElSearchWithFilter from "@/Components/Form/ElSearchWithFilter.vue";
import {usePage} from "@inertiajs/vue3";

const props = defineProps({
    showBreadcrumb: {
        type: Boolean,
        default: true
    },
    showFilters: {
        type: Boolean,
        default: true
    },
    isProfilePage: Boolean,
})

</script>
