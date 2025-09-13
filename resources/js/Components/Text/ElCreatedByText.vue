<template>

    <div v-if="model && model.id && model.created_by">
        <ElRouteUserProfile :model="model.created_by" v-if="model.created_by_type ==='App\\Models\\User'"/>
        <!-- <ClientProfile :model="model.created_by" v-if="model.created_by_type ==='App\\Models\\Client'"/> -->
    </div>
    <ElText v-else/>

</template>

<script setup>

import ElRouteUserProfile from "@/Components/Profile/ElRouteUserProfile.vue";
// import ClientProfile from "@/Components/Profile/ClientProfile.vue";

const props = defineProps({
    model: {type: Object, default: null},
})

</script>
