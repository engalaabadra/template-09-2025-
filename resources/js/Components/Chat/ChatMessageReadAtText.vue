<template>

    <div v-if="message?.read_at && message.auth_is_from_model">
        {{ message.read_at }}
        <i class="pi pi-check ml-1 !text-[.7rem] !text-green-500"></i>
    </div>

</template>

<script setup>

const props = defineProps({
    message: {type: Object, default: null},
})

</script>

<style>
</style>
