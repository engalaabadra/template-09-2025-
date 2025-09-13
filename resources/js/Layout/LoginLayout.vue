<template>

    <PageHead/>

    <slot/>

    <NotificationAlert/>

</template>

<script setup>

import NotificationAlert from "@/Layout/Partial/NotificationAlert.vue";
import PageHead from "@/Layout/Partial/PageHead.vue";
</script>
