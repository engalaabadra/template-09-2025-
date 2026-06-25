<template>

    <span v-if="!isEmpty()" class="flex items-center gap-1">

        <span v-if="elId" v-text="'#'+elId"/>
        <span v-if="isDescription" v-html="value"></span>
        <span v-else>{{value}}</span>

        <span v-if="append">{{ append }}</span>
        <slot name="append"/>

        <!-- <SvgCurrency v-if="showCurrency"/> -->

    </span>

    <span v-else-if="!hiddenEmpty" v-text="'---'"/>

</template>

<script setup>

// import SvgCurrency from "@/Components/Svg/SvgCurrency.vue";

const props = defineProps({
    value: {type: [String, Number], default: null},
    append: {type: String, default: null},
    zeroToNull: {type: Boolean, default: true},
    elId: {type: String, default: null},
    hiddenEmpty: {type: Boolean, default: false},
    isDescription: {type: Boolean, default: false},
    showCurrency: Boolean,
})

const isEmpty = () => {
    if ((props.value === '0' || props.value === 0) && props.zeroToNull) {
        return true;
    }

    return props.value == null || props.value === '';
}

</script>
