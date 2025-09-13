<template>

    <ElRoute v-if="model && model.id"
        :href="route('dashboard.users.profile.main-data',model?.id)">

        <template #avatar>
            <Avatar
                v-if="showAvatar"
                :image="model.avatar_url"
                class="shadow-sm shadow-5xl ml-3"
                size="large"
            />
        </template>

        <template #title> {{ model[props.name] }}</template>

        <template #description>
            <div class="flex gap-4">

                <div v-if="showEmail">
                    {{ model.job_position ?? model.email }}
                </div>

                <Button icon="pi pi-comments" v-if="showStartChat && usePage().props.enable_chat" severity="info" aria-label="User"
                        class="p-button-rounded p-button-text p-button-sm !h-[18px] !w-[18px] p-0!"
                        @click.stop.prevent="ref_new_chat_dialog.showDialog('App\\Models\\User',model.id,model.name)"
                        v-tooltip.top="$t('message.send_message')"/>
            </div>
        </template>

    </ElRoute>

    <ElText v-else/>
    <NewChatDialog ref="ref_new_chat_dialog"/>

</template>

<script setup>

import Avatar from 'primevue/avatar';
import ElRoute from "@/Components/ElRoutes/ElRoute.vue";
import ElText from "@/Components/Text/ElText.vue";
import {usePage} from "@inertiajs/vue3";
import Button from "primevue/button";
import NewChatDialog from "@/Components/Chat/NewChatDialog.vue";
import {ref} from "vue";
const ref_new_chat_dialog=ref();

const props = defineProps({
    model: {type: Object, default: null},
    name: {type: String, default: 'name'},
    showAvatar: {type: Boolean, default: true},
    showEmail: {type: Boolean, default: true},
    showStartChat: {type: Boolean, default: true},
})

</script>
