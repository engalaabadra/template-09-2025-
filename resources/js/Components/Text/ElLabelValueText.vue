<template>
    <div class="flex flex-wrap items-center gap-1" v-if="value || !hideEmpty">
        <span v-if="label" class="text-nowrap">{{ label }} : </span>
        <div v-if="!$slots.value">
            <ElAOrText :append="append" v-if="href" :href="href" :label="value"/>
            <ElTextDescription :append="append" v-else-if="isDescription" :value="value"/>
            <ElText :append="append" v-else-if="!isPrice && !isPercentage" :value="value" :zeroToNull="zeroToNull"/>
            <ElPhone v-else-if="isPhone" :value="value"/>
            <ElPricePercentage v-else :value="value" :is-price="isPrice"/>
        </div>
        <slot name="value"/>
    </div>
</template>

<script setup>
import ElPricePercentage from "@/Components/Text/ElPricePercentage.vue";
import ElAOrText from "@/Components/Text/ElAOrText.vue";
import ElPhone from "@/Components/Text/ElPhone.vue";
import ElTextDescription from "@/Components/Text/ElTextDescription.vue";

const props = defineProps({
    label: {type: String, default: null},
    value: {type: [String, Number], default: null},
    append: {type: String, default: null},
    isPrice: {type: Boolean, default: false},
    isPercentage: {type: Boolean, default: false},
    isPhone: {type: Boolean, default: false},
    isDescription: {type: Boolean, default: false},
    hideEmpty: {type: Boolean, default: false},
    href: {type: String, default: null},
    zeroToNull: {type: Boolean, default: true},
})
</script>


<style scoped>

</style>
