<template>
    <div class="flex items-center gap-1" v-if="value || !hideEmpty">
        <span v-if="label">{{ label }} : </span>
        <SvgTrueFalse v-if="value!==null" :value="value"/>
        <ElText v-else/>
        <span v-if="append">{{ append }}</span>
    </div>
</template>

<script setup>
import SvgTrueFalse from "@/Components/Svg/SvgTrueFalse.vue";

const props = defineProps({
    label: {type: String, default: null},
    value: {type: Boolean, default: null},
    append: {type: String, default: null},
    hideEmpty: {type: Boolean, default: false},
})
</script>


<style scoped>

</style>
