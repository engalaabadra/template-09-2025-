<template>

    <img
        v-bind="$attrs"
        :src="asset('images/svg_saudi_riyal_symbol'+(isLight?'_light':'')+'.svg')"
        class="inline-block w-4"
        alt="Currency"
    />

</template>

<script setup>

import {asset} from "@/Helpers/Functions.js";
const props = defineProps({
    isLight: {type: Boolean, default: false}
})
</script>
