<template>

    <div class="flex" v-if="usePage().props.enable_chat">
        <NotificationIcon @click="getChats()" icon="pi pi-comments"
                          :count="authUser()['not_read_chat_messages_count'] || 0"/>
    </div>

    <Drawer
        v-model:visible="chat_drawer_visible"
        :header="$t('message.your_chats')"
        class="w-full! md:w-[800px]!"
        pt:header="bg-gray-100"
        pt:content="p-3! flex flex-col gap-3">
        <div v-if="!selected_chat" class="h-full flex flex-col">
            <div class="flex w-full justify-between mb-2">
                <div class=" relative p-input-icon-left text-dark-500">
                    <span class="absolute top-[12px] start-[14px] dark:text-gray-400" style="z-index: 3333">
                        <i v-if="!show_progress" class="pi pi-search"/>
                        <i v-else class="pi pi-spin pi-spinner"/>
                    </span>
                    <InputText v-model="chat_filter.search" class="w-full ps-8!" size="small"
                               :placeholder="$t('message.search_here')"/>
                </div>

                <ElPrimaryButton icon="pi pi-plus" @click="ref_new_chat_dialog.showDialog()"

                                 :text="$t('message.new_chat')"/>
            </div>

            <div ref="ref_chat_container"
                 class="overflow-y-auto flex-grow"
                 @scroll="handleChatListScroll">
                <div v-if="chats && chats?.data?.length">
                    <div v-for="chat in chats.data" :key="chat.id"
                         class="border rounded p-3 flex justify-between items-center gap-3 mb-2 cursor-pointer hover:bg-gray-50 "
                         :class="{'bg-gray-100':chat.auth_party_unread_messages_count}">
                        <section class="min-w-[220px]" @click="selectChat(chat)">
                            <!-- <ChatModelProfile :model="chat" :is-from-model="chat.auth_is_to_model"/> -->
                            <div class="flex gap-1">
                                <Badge @click.stop.prevent="ref_transfer_chat_dialog.showDialog(chat)"
                                       v-if="authUser()?.type_normal"
                                       :value="$t('message.transfer_chat')" severity="info"/>

                                <Badge @click.stop.prevent="ref_toggle_close_chat_dialog.showDialog(chat)"
                                       v-if="chat.status!==Enum.ChatStatusEnum.CLOSED"
                                       :value="$t('message.close_chat')" severity="danger"/>

                                <Badge @click.stop="ref_toggle_close_chat_dialog.showDialog(chat)"
                                       v-if="chat.status===Enum.ChatStatusEnum.CLOSED"
                                       :value="$t('message.open_chat')" severity="primary"/>
                                <aside class="flex items-center gap-1" v-if="chat.allow_whatsapp">
                                    <i class="pi pi-whatsapp text-green-400" style="font-size: 1.3rem"></i>
                                </aside>
                            </div>
                        </section>
                        <section @click="selectChat(chat)" class="flex-grow">
                            <div class="text-xs text-gray-400 text-end flex flex-col">
                                <div class="text-sm overflow-hidden break-all line-clamp-2 text-gray-500">
                                    {{ chat.last_message?.message || $t('message.no_messages') }}
                                </div>
                                <div>
                                    <ChatMessageReadAtText v-if="chat.last_message.read_at && chat.last_message.auth_is_from_model" :message="chat.last_message"/>
                                    <div v-else>
                                        {{ chat.updated_at_text }}
                                    </div>
                                </div>
                                <div class="text-end flex gap-1 justify-end">

                                    <ChatStatus :chat="chat"/>
                                    <Badge v-if="chat.auth_party_unread_messages_count"
                                           :value="chat.auth_party_unread_messages_count" class="!pt-1" severity="danger"/>
                                </div>
                            </div>
                        </section>
                    </div>

                    <!-- Loading more chats indicator -->
                    <div v-if="loadingMoreChats" class="flex justify-center my-3">
                        <ProgressSpinner style="width: 30px; height: 30px;"/>
                    </div>
                </div>

                <div v-if="show_progress" class="w-full h-full flex justify-center items-center">
                    <ProgressSpinner/>
                </div>

                <div v-if="!show_progress && !chats.data?.length"
                     class="w-full h-full flex justify-center items-center">
                    <!-- <EmptyData :emptyTitle="$t('message.no_chats_title')" :is-large="false" hideMessage/> -->
                </div>
            </div>
        </div>

        <!-- Chat Detail View -->
        <div v-else class="flex flex-col h-full">
            <div class="flex justify-between items-center mb-3 pb-2 border-b">
                <div class="flex items-center gap-2">
                    <Button icon="pi pi-arrow-left" class="p-button-text p-button-sm"
                            @click="selected_chat = null;getChats()"/>
                    <div>
                        <!-- <ChatModelProfile :model="selected_chat" :is-from-model="selected_chat.auth_is_to_model"/> -->
                    </div>
                </div>
                <ChatStatus :chat="selected_chat"/>
            </div>

            <div
                ref="ref_messages_container"
                class="flex-grow overflow-y-auto mb-3 max-h-[calc(100vh-250px)]"
                v-if="!show_progress"
                @scroll="handleMessageScroll"
            >
                <!-- Loading more messages indicator -->
                <div v-if="loading_more_messages" class="flex justify-center my-3">
                    <ProgressSpinner style="width: 30px; height: 30px;"/>
                </div>

                <div v-if="selected_chat_messages?.data?.length" class="flex flex-col gap-2">
                    <div v-for="message in selected_chat_messages?.data" :key="message.id"
                         class="flex flex-row-reverse">
                        <div class="p-3 rounded-lg flex-grow max-w-[75%]"
                             :class="{
                                'bg-gray-100 ml-auto': message.auth_is_from_model,
                                'bg-gray-50': !message.auth_is_from_model
                            }">
                            <div class="text-sm">{{ message.message }}</div>
                            <div class="flex justify-between text-xs text-gray-500 mt-1">
                                <div>
                                    {{ message.created_at_text }}
                                </div>
                                <ChatMessageReadAtText :message="message"/>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-else class="flex justify-center items-center h-full">
                    <!-- <EmptyData :emptyTitle="$t('message.no_messages_title')" hideMessage/> -->
                </div>
            </div>
            <div v-if="show_progress" class="w-full h-full flex justify-center items-center">
                <ProgressSpinner/>
            </div>

            <div v-if="!show_progress && selected_chat.status!==Enum.ChatStatusEnum.CLOSED" class="mt-auto">
                <form @submit.prevent="submitSendMessage">
                    <div class="flex items-center gap-2">
                        <ElFloatingInput :form="el_new_message_form" name="message"
                                         class="flex-grow"
                                         :label="$t('message.type_message')" autofocus/>
                        <Button type="submit" icon="pi pi-send"/>
                    </div>
                </form>
            </div>
        </div>
    </Drawer>

    <NewChatDialog ref="ref_new_chat_dialog" @success="getChats"/>
    <MoveChatDialog ref="ref_transfer_chat_dialog" @success="getChats"/>
    <ToggleCloseChatDialog ref="ref_toggle_close_chat_dialog" @success="getChats"/>
    <audio id="ringtone_message2_chat_drawer" src="/sounds/message2.wav" class="hidden"></audio>

</template>

<script setup>

import {nextTick, onMounted, onUnmounted, ref, watch} from "vue";
import ProgressSpinner from 'primevue/progressspinner';
import {useForm, usePage} from "@inertiajs/vue3";
import Drawer from "primevue/drawer";
import NotificationIcon from "@/Layout/Partial/NotificationIcon.vue";
// import EmptyData from "@/Components/EmptyData.vue";
import Button from 'primevue/button';
import Badge from 'primevue/badge';
import NewChatDialog from "@/Components/Chat/NewChatDialog.vue";
// import ChatModelProfile from "@/Components/Chat/ChatModelProfile.vue";
import ElFloatingInput from "@/Components/Form/ElFloatingInput.vue";
import {authId, authIsClient, authUser, modelCreatedByAuth} from "@/Helpers/Auth.js";
import ElPrimaryButton from "@/Components/Buttons/ElPrimaryButton.vue";
import {Enum} from "@/enum.js";
import ChatStatus from "@/Components/Chat/ChatStatus.vue";
import MoveChatDialog from "@/Components/Chat/MoveChatDialog.vue";
import ToggleCloseChatDialog from "@/Components/Chat/ToggleCloseChatDialog.vue";
import {subscribeToChannel} from "@/Helpers/Realtime.js";
import InputText from "primevue/inputtext";
import {useI18n} from "vue-i18n";
import ChatMessageReadAtText from "@/Components/Chat/ChatMessageReadAtText.vue";

const ref_new_chat_dialog = ref();
const ref_transfer_chat_dialog = ref();
const ref_toggle_close_chat_dialog = ref();
const ref_chat_container = ref();
const ref_messages_container = ref();


let chats = ref([]);
let chat_drawer_visible = ref(false);
let show_progress = ref(true);
let currentPage = ref(1);
let hasMoreChats = ref(true);
let loadingMoreChats = ref(false);

// Chat detail
let selected_chat = ref([]);
let selected_chat_messages = ref([]);
let currentMessagePage = ref(1);
let has_more_messages = ref(true);
let loading_more_messages = ref(false);

let chat_subscription = null;
const chat_filter = useForm({
    search: null
});
const ringtoneAudio = ref(null);
onMounted(() => {
    ringtoneAudio.value = document.getElementById('ringtone_message2_chat_drawer');
});

watch(chat_filter, (search) => {
    getChats();
})
const getChats = (page = 1, append = false) => {
    if (page === 1) {
        chats.value = [];
    }

    if (page === 1 || !append) {
        show_progress.value = true;
    } else {
        loadingMoreChats.value = true;
    }

    chat_drawer_visible.value = true;
    if (page === 1) {
        selected_chat.value = null;
    }

    currentPage.value = page;

    return axios.post(route('chat.index', {
        type: 'get_chats',
        page: page,
        search: chat_filter.search
    })).then(response => {
        const newChats = response.data.chats;

        if (append && chats.value && chats.value.data) {
            chats.value.data = [...chats.value.data, ...newChats.data];
        } else {
            chats.value = newChats;
        }
        if (usePage().props.auth?.user) {
            usePage().props.auth.user.not_read_chat_messages_count = response.data.auth_user_not_read_chat_messages_count || 0;
        }
        hasMoreChats.value = newChats.current_page < newChats.last_page;
        show_progress.value = false;
        loadingMoreChats.value = false;

        return newChats;
    });
}


chat_subscription = subscribeToChannel(authIsClient() ? `client.${authId()}` : `user.${authId()}`, '.chat.new_message', (event) => {
    if (!modelCreatedByAuth(event.message)) {
        ringtoneAudio.value.currentTime = 0
        ringtoneAudio.value.play();
    }
    getChats();
});
const getSelectedChatMessages = (page = 1, append = false) => {
    if (page === 1 && !append) {
        selected_chat_messages.value = [];
    }

    if (!selected_chat.value.id)
        return;

    if (page === 1 && !append) {
        show_progress.value = true;
    } else {
        loading_more_messages.value = true;
    }

    currentMessagePage.value = page;

    axios.post(route('chat.index', {
        type: 'show_chat',
        chat_id: selected_chat.value.id,
        page: page
    })).then(response => {
        const new_messages = response.data.messages;
        selected_chat.value = response.data.chat;

        if (append && selected_chat_messages.value && selected_chat_messages.value.data) {
            selected_chat_messages.value.data = [...new_messages.data, ...selected_chat_messages.value.data];
        } else {
            selected_chat_messages.value = new_messages;
        }

        has_more_messages.value = new_messages.current_page < new_messages.last_page;
        if (page === 1) {
            scrollRefMessageContainerToBottom();
        }
        show_progress.value = false;
        loading_more_messages.value = false;

        // Update the notification count in the page props
        if (usePage().props.auth?.user) {
            usePage().props.auth.user.not_read_chat_messages_count = response.data.auth_user_not_read_chat_messages_count || 0;
        }
    });
}
const scrollRefMessageContainerToBottom = async () => {
    setTimeout(function () {
        if (ref_messages_container.value) {
            ref_messages_container.value.scrollTop = ref_messages_container.value.scrollHeight;
        }
    }, 100)
};


const handleChatListScroll = () => {
    if (!ref_chat_container.value || loadingMoreChats.value || !hasMoreChats.value) return;

    const {scrollTop, scrollHeight, clientHeight} = ref_chat_container.value;
    if (scrollHeight - scrollTop - clientHeight < 50) {
        getChats(currentPage.value + 1, true);
    }
};

const handleMessageScroll = () => {
    if (!ref_messages_container.value || loading_more_messages.value || !has_more_messages.value) return;

    const {scrollTop} = ref_messages_container.value;
    if (scrollTop < 50) {
        const scrollHeight = ref_messages_container.value.scrollHeight;

        getSelectedChatMessages(currentMessagePage.value + 1, true);

        nextTick(() => {
            if (ref_messages_container.value) {
                const newScrollHeight = ref_messages_container.value.scrollHeight;
                ref_messages_container.value.scrollTop = newScrollHeight - scrollHeight;
            }
        });
    }
};


const selectChat = (chat) => {
    if (chat_subscription) {
        chat_subscription.unsubscribe();
        chat_subscription = null;
    }

    selected_chat.value = chat;
    currentMessagePage.value = 1;
    has_more_messages.value = true;
    getSelectedChatMessages();

    chat_subscription = subscribeToChannel(`chat.${chat.id}`, '.chat.new_message', (event) => {
        if (!modelCreatedByAuth(event.message)) {
            ringtoneAudio.value.currentTime = 0
            ringtoneAudio.value.play();
        }
        refreshSelectedChat();
    });
}

const openChatDrawerHandler = (event) => {
    const isNewChat = event.detail?.newChat === true;

    chat_drawer_visible.value = true;

    // Get chats and then select the most recent one (first in the list)
    getChats().then(newChats => {
        if (isNewChat && newChats && newChats.data && newChats.data.length > 0) {
            selectChat(newChats.data[0]);
        }
    }).catch(error => {
        console.error('Error loading chats:', error);
    });
};

onMounted(() => {
    window.addEventListener('open-chat-drawer', openChatDrawerHandler);
});

onUnmounted(() => {
    window.removeEventListener('open-chat-drawer', openChatDrawerHandler);

    if (chat_subscription) {
        chat_subscription.unsubscribe();
        chat_subscription = null;
    }
});

const el_new_message_form = useForm({
    message: null
});
const {t} = useI18n();

const refreshSelectedChat = () => {
    axios.post(route('chat.index', {
        type: 'show_chat',
        chat_id: selected_chat.value.id,
        page: 1
    })).then(response => {
        selected_chat.value = response.data.chat;
        selected_chat_messages.value = response.data.messages;

        scrollRefMessageContainerToBottom();

        // Update the notification count in the page props
        if (usePage().props.auth?.user) {
            usePage().props.auth.user.not_read_chat_messages_count = response.data.auth_user_not_read_chat_messages_count || 0;
        }
    });
}
const submitSendMessage = () => {
    // Create a temporary message object with the current form data
    const tempMessage = {
        id: 'temp-' + Date.now(),
        message: el_new_message_form.message,
        created_at_text: t('message.sending'),
        auth_is_from_model: true,
        read_at: null
    };

    // Add the temporary message to the list for immediate feedback
    if (selected_chat_messages.value && selected_chat_messages.value.data) {
        selected_chat_messages.value.data.push(tempMessage);

        scrollRefMessageContainerToBottom();
    }

    // Send the message to the server
    el_new_message_form.post(route('chat.messages.store', selected_chat.value.id), {
        onSuccess: () => {
            refreshSelectedChat();

            el_new_message_form.reset();
        }
    })
}

</script>

<style>
</style>
