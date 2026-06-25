<template>
    <div v-if="model" class="flex gap-1">
<!--        <span v-if="model.city_name_text">{{ model.city_name_text }}</span>-->
<!--        <span v-if="model.city_name_text && model.block_name_text">-</span>-->
        <span v-if="model.block_name_text">{{ model.block_name_text }}</span>
    </div>
</template>

<script setup>

const props = defineProps({
    model: {type: Object, default: null},
})

</script>
