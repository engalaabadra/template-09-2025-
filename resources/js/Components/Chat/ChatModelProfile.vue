<template>
    <div v-if="model && model.id && getChatModel()">
        <template v-if="!isCircle">
            <ElRouteUserProfile v-if="'App\\Models\\User' === model.to_model_type"
                                :show-start-chat="false"
                                :model="getChatModel()" name="name2"/>
                                
            <ClientProfile v-else-if="'App\\Models\\Client' === model.to_model_type"
                           :show-start-chat="false"
                           :model="getChatModel()"/>
        </template>
        <template v-else>
            <Avatar :label="getChatModel()?.name?.charAt(0)" class="mr-2" size="large"
                    style="background-color: #ece9fc; color: #2a1261" shape="circle"/>
        </template>
    </div>
</template>

<script setup>
import ElRouteUserProfile from "@/Components/Profile/ElRouteUserProfile.vue";
import ClientProfile from "@/Components/Profile/ClientProfile.vue";
import Avatar from 'primevue/avatar';

const props = defineProps({
    model: Object,
    isFromModel: {type: Boolean, default: false},
    isCircle: {type: Boolean, default: false},
});
const getChatModel = () => {
    return props.model[props.isFromModel ? 'from_model' : 'to_model'];
}
</script>


