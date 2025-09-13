<template>

    <div class="flex gap-2">

        <span v-text="$t('column.created_at') + ' :'"/>

        <ElText :value="model.created_at_text"/>

    </div>

</template>

<script setup>

const props = defineProps({
    model: {type: [String, Object], default: null},
})

</script>
