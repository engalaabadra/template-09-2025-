<template>
    <Badge v-if="chat.status_text" :value="chat.status_text" :severity="getSeverity()"/>
</template>

<script setup>

import Badge from 'primevue/badge';
import {Enum} from "@/enum.js";

const props=defineProps({
    chat:Object
})
const getSeverity = () => {
    let status=props.chat?.status;
    if (status===Enum.ChatStatusEnum.CLOSED)
        return 'success';
    if (status===Enum.ChatStatusEnum.OPEN)
        return 'warn';

    if (status===Enum.ChatStatusEnum.IN_PROGRESS)
        return 'info';

    return 'success';
}
</script>

<style>
</style>
