<template>

    <div
        class="flex justify-center items-center gap-4 w-full p-8 rounded-lg bg-white"
        :class="{ 'flex-col text-center': isLarge }"
    >
        <img
            :src="asset('images/data-empty.svg')"
            alt="No Data"
            :class="{ 'h-44': !isLarge }"
        />

        <div class="flex flex-col gap-4">

            <h3 class="text-xl font-bold" v-text="emptyTitle ?? $t('message.data_empty_title')"/>
            <p v-if="!hideMessage" v-text="emptyMessage ?? $t('message.data_empty_description')"/>

        </div>

    </div>

</template>

<script setup>

import {asset} from "@/Helpers/Functions.js";

const props = defineProps({
    isLarge: {type: Boolean, default: true},
    emptyTitle: {type: String, default: null},
    emptyMessage: {type: String, default: null},
    hideMessage: {type: Boolean, default: false},
});

</script>
