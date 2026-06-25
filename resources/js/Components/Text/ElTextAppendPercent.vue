<template>

    <span v-if="$slots.default">
        <slot/>
    </span>

    <span v-else v-text="(value == null || (value == '' && value != '0') ||(value == '' && zeroToNull) ? '0' : value) + ' %'" />

</template>

<script setup>

const props = defineProps({
    value: {type: [String, Number], default: null},
    zeroToNull: {type: Boolean, default: true}
});

</script>
