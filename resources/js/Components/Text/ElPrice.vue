<template>

    <span class="whitespace-nowrap">

        <span v-if="value && value!=0" class="flex items-center gap-2">

            {{ numberFormat(value) }}

            <!-- <SvgCurrency :is-light="currencyIsLight"/> -->

        </span>

        <ElText v-else/>

    </span>

</template>

<script setup>

// import SvgCurrency from "@/Components/Svg/SvgCurrency.vue";

const props = defineProps({
    value: [String, Number],
    currencyIsLight:{
        type:Boolean,
        default:false,
    }
});

function numberFormat(number) {
    return new Intl.NumberFormat('en-SA').format(number);
}

</script>
