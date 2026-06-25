<template>
    <ElText :value="$t('message.file_not_found')">
        <template #append>
            <span class="text-red-500 text-xl">!</span>
        </template>
    </ElText>

</template>

<script setup>

const props = defineProps({

})

</script>
