<template>

    <div class="flex">

        <NotificationIcon
            @click="showNotification('general')"
            icon="pi pi-bell"
            :count="authUser()['not_read_notification_count'] || 0"/>

    </div>

    <Drawer
        v-model:visible="notificationsShow"
        :header="$t('message.notifications')"
        class="w-full! md:w-[500px]!"
        pt:header="bg-gray-100"
        pt:content="p-3! flex flex-col gap-3"
        @show="setupScrollListener">

        <div>
            <Link class="cursor-pointer text-blue-500" v-text="$t('message.mark_all_read')"
                  v-if="notifications.length && notifications.some(n => !n.read_at)"
                  @click="markAllRead()"/>
        </div>
        <div v-if="notifications.length" v-for="notification in notifications"
             class="border rounded p-3 flex justify-center  items-center gap-3"
             :class="{'bg-gray-100':!notification?.read_at}">

            <div class="bg-gray-100 w-10 h-10 grow rounded flex justify-center items-center">
                <i :class="notification.data.icon ?? 'pi pi-bell'"/>
            </div>

            <div class="w-[calc(100%-40px)]">

                <Link
                    v-if="notification.data.url"
                    :href="notification.data.url"
                    class="text-primary-500 mb-1 text-md font-semibold hover:text-blue-500"
                    @click="notificationsShow = false;"
                >
                    {{ notification.data.message }}
                </Link>

                <span
                    v-else
                    class="text-primary-500 mb-1 text-md font-semibold"
                    v-text="notification.data.message"
                />

                <div class="flex justify-between text-slate-400 text-sm">

                    <ElRouteUserProfile
                        v-if="'App\\Models\\User' === notification.created_by_type"
                        :show-email="false"
                        :model="notification.created_by"
                        :show-avatar="false"
                    />

                    <span v-else v-text="notification.created_by?.name"/>

                    <div>
                        <span v-text="notification.created_at_text" class="text-xs"/>
                        <ElText class="cursor-pointer text-blue-500" :value="$t('message.mark_as_read')"
                                v-if="!notification.read_at" @click="markNotificationRead(notification)"/>
                        <ElText class="cursor-pointer text-red-500" :value="$t('message.mark_as_not_read')" v-else
                                @click="markNotificationNotRead(notification)"/>
                    </div>

                </div>

            </div>

        </div>

        <div
            v-if="showProgress"
            class="w-full h-full flex justify-center items-center"
        >
            <ProgressSpinner/>
        </div>

        <div v-if="!showProgress && !notifications.length" class="w-full h-full flex justify-center items-center">
            <!-- <EmptyData
                :emptyTitle="$t('message.no_notifications_title')"
                hideMessage
            /> -->
        </div>

        <!-- Loading more indicator -->
        <div v-if="loadingMore && !showProgress" class="flex justify-center py-2">
            <ProgressSpinner style="width: 30px; height: 30px;"/>
        </div>

    </Drawer>
    <audio id="ringtone" src="/sounds/message-ring.mp3" class="hidden"></audio>

</template>

<script setup>

import {onMounted, onUnmounted, ref, nextTick} from "vue";
import ProgressSpinner from 'primevue/progressspinner';
import {Link, useForm, usePage} from "@inertiajs/vue3";
import Drawer from "primevue/drawer";
import ElRouteUserProfile from "@/Components/Profile/ElRouteUserProfile.vue";
import NotificationIcon from "@/Layout/Partial/NotificationIcon.vue";
// import EmptyData from "@/Components/EmptyData.vue";
import {subscribeToChannel} from "@/Helpers/Realtime.js";
import {authId, authIsUser, authUser} from "@/Helpers/Auth.js";
import {getLocal} from "@/Helpers/Local.js";
import {useToast} from "primevue/usetoast";

const toast = useToast();
let notifications = ref([]);
let notificationsShow = ref(false);
let showProgress = ref(true);
let loadingMore = ref(false);
const group = ref();
const page = ref(1);
const hasMorePages = ref(true);
const drawerContent = ref(null);

const showNotification = (el_group) => {
    group.value = el_group;
    notifications.value = [];
    showProgress.value = true;
    page.value = 1;
    hasMorePages.value = true;
    notificationsShow.value = true;

    loadNotifications();
}

const loadNotifications = () => {
    if (!hasMorePages.value || loadingMore.value) return;

    loadingMore.value = true;

    axios.get(route('dashboard.users.notifications.index', group.value), {
        params: {
            page: page.value,
            per_page: 10
        }
    }).then(response => {
        if (page.value === 1) {
            notifications.value = response.data.notifications;
        } else {
            notifications.value = [...notifications.value, ...response.data.notifications];
        }

        hasMorePages.value = response.data.pagination.current_page < response.data.pagination.last_page;
        page.value++;
        showProgress.value = false;
        loadingMore.value = false;
    }).catch(() => {
        loadingMore.value = false;
    });
}

const handleScroll = (event) => {
    const target = event.target;
    const scrollPosition = target.scrollTop + target.clientHeight;
    const scrollHeight = target.scrollHeight;

    // Load more when user scrolls to 80% of the content
    if (scrollPosition >= scrollHeight * 0.8 && !loadingMore.value && hasMorePages.value) {
        loadNotifications();
    }
}
let notification_subscription = null;
const ringtoneAudio = ref(null);

const setupScrollListener = () => {
    nextTick(() => {
        // Find the drawer content element
        const drawerContentElement = document.querySelector('.p-drawer-content');
        if (drawerContentElement) {
            drawerContentElement.removeEventListener('scroll', handleScroll);
            drawerContentElement.addEventListener('scroll', handleScroll);
            drawerContent.value = drawerContentElement;
        }
    });
};

onMounted(() => {
    ringtoneAudio.value = document.getElementById('ringtone');
});

onUnmounted(() => {
    if (drawerContent.value) {
        drawerContent.value.removeEventListener('scroll', handleScroll);
    }
});

if (authIsUser()) {
    notification_subscription = subscribeToChannel(`user.${authId()}`, '.notification.new_notification', (response) => {
        usePage().props.auth.user.not_read_notification_count = response.not_read_notification_count || 0;

        ringtoneAudio.value.currentTime = 0
        ringtoneAudio.value.play();
        toast.add({
            severity: 'info',
            detail: response.message[getLocal()],
            link_href: response.url,
            mark_as_read_url: response.mark_as_read_url,
            group: 'tl', life: 10000
        });
    });
}

onUnmounted(() => {
    if (notification_subscription) {
        notification_subscription.unsubscribe();
        notification_subscription = null;
    }
});
const markNotificationRead = (notification) => {
    if (notification.id && !notification.read_at) {
        notification.read_at = true;
        useForm().post(route('dashboard.users.notifications.mark-as-read', notification.id));
    }
}
const markNotificationNotRead = (notification) => {
    if (notification.id && notification.read_at) {
        notification.read_at = null;
        useForm().post(route('dashboard.users.notifications.mark-as-not-read', notification.id));
    }
}
const markAllRead = () => {
    notifications.value = notifications.value.map(notification => ({
        ...notification,
        read_at: notification.read_at ? notification.read_at : true
    }));

    useForm().post(route('dashboard.users.notifications.mark-all-read', 'general'), {
        preserveScroll: true,
        onSuccess: () => {
            loadNotifications();
        },
        onError: (error) => console.error(error),
    });
}
</script>

<style>
@keyframes p-progress-spinner-color {
    100%,
    0% {
        stroke: #ce8e54;
    }
    40% {
        stroke: #ce8e54;
    }
    66% {
        stroke: #ce8e54;
    }
    80%,
    90% {
        stroke: #ce8e54;
    }
}
</style>
