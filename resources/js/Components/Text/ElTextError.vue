<script setup>
const props=defineProps({
    value:String,
});
</script>

<template>
    <span class="text-red-500 pt-1 block">
        {{ value}}
    </span>
</template>

<style scoped>

</style>
