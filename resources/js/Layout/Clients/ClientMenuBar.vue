<template>

    <Menubar
        :model="$page.props.menu"
        pt:root="px-8! py-0! min-h-16"
        pt:start="ltr:mr-5 rtl:ml-5"
        :pt="{
            submenu: 'z-99999!',
        }"
    >

        <template #start>

            <img
                :src="asset('images/state-logo.svg')"
                alt="logo"
                class="h-full max-h-[30px]"
            >

        </template>

        <template #item="{ item, props, hasSubmenu, root }">

            <Link
                v-if="item.href"
                :href="item.href"
                class="flex justify-start items-center gap-1 cursor-pointer text-sm font-bold h-16 px-1 border-b-3 border-b-transparent"
                :class="{
                    'border-b-3 border-secondary-500! text-secondary-500': item.active && root,
                    'border-b-3 border-secondary-500 text-secondary-500': item.active && root,
                    'gap-2 px-5 py-3 h-auto!': !root
                }"
            >

                <i class="pi text-sm" :class="item.icon"/>

                <span>{{ item.label }}</span>

                <Badge v-if="item.badge" :class="{ 'ml-auto': !root, 'ml-2': root }" :value="item.badge"/>

            </Link>

            <div
                v-else
                class="flex justify-start items-center gap-1 cursor-pointer text-sm font-bold h-16 px-1 border-b-3 border-b-transparent"
                :class="{
                    'border-b-3 border-secondary-500 text-secondary-500': item.active && root,
                    'bg-secondary-50 text-secondary-500!': item.active && !root
                }"
            >

                <i class="pi text-sm" :class="item.icon"/>

                <span>{{ item.label }}</span>

                <Badge v-if="item.badge" :class="{ 'ml-auto': !root, 'ml-2': root }" :value="item.badge"/>

            </div>

        </template>

        <template #end>

            <div
                class="flex gap-x-4 space-x-2 space-x-reverse shrink-0 justify-center items-center content-center leading-5 text-slate-900"
            >

                <Popover ref="ref_popover" style="width: max-content" :breakpoints="{'960px': '75vw'}">
                    <ClientMenu/>
                </Popover>

                <!-- <ElChangeLanguageSelect/> -->
                <ChatDrawer/>
                <div
                    class="flex space-x-2 space-x-reverse items-center bg-gray-50 dark:bg-slate-500/20 py-1 px-1.5 rounded-lg cursor-pointer text-slate-900 dark:text-primary-100"
                    @click="togglePopover"
                >

                    <span class="text-sm" v-text="$page.props.auth.client?.name"/>

                    <i class="pi pi-chevron-down text-neutral-400" style="font-size: 1rem"></i>

                </div>

            </div>

        </template>

    </Menubar>

</template>

<script setup>

import {ref} from "vue";
import {Badge, Menubar} from "primevue";
import {asset} from "@/Helpers/Functions.js";
// import Popover from "primevue/popover";
// import ElChangeLanguageSelect from "@/Components/Main/ElChangeLanguageSelect.vue";
import ClientMenu from "@/Layout/Clients/ClientMenu.vue";
import ChatDrawer from "@/Components/Chat/ChatDrawer.vue";

// const ref_popover = ref();

// const togglePopover = (event) => {
//     ref_popover.value.toggle(event);
// }

</script>
