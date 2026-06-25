<template>
    <ElPrice v-if="isPrice" :value="value"/>
    <ElText v-else :value="value + ' %'"/>
</template>

<script setup>
import ElPrice from "@/Components/Text/ElPrice.vue";
import ElText from "@/Components/Text/ElText.vue";

const props = defineProps({
    value: [String, Number],
    isPrice: {type: Boolean, default: true}
});

</script>

<style scoped>

</style>
