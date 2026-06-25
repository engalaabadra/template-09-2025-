<template>
    <ElText :value="(value ?? 0) + ' %'"/>
</template>

<script setup>
import ElText from "@/Components/Text/ElText.vue";

const props = defineProps({
    value: [String, Number],
});

</script>

<style scoped>

</style>
