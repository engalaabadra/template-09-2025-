<script setup>
import {ref} from "vue";
import ElSearchInput from "@/Components/Form/ElSearchInput.vue";
import UserMenu from "@/Layout/Partial/UserMenu.vue";

// const ref_popover = ref();
const emit = defineEmits(['toggle_aside_show_state'])
// const togglePopover=(event) => {
//     ref_popover.value.toggle(event);
// }

</script>

<template>
    <section
        class="print:hidden flex justify-between items-stretch py-3 leading-5 text-slate-900 dark:text-primary-100 bg-white dark:bg-dark-300">
        <div
            class="flex justify-between items-center px-2 mx-auto w-full leading-5 text-slate-900 lg:px-8">
            <aside @click="$emit('toggle_aside_show_state');"
                   class="bg-slate-500/10 cursor-pointer grid lg:hidden place-items-center py-1.5 px-2 rounded-lg">
                <i class="pi pi-bars text-xl"></i>
            </aside>
            <div class="flex space-x-3 space-x-reverse flex-wrap justify-center items-center
                leading-5 text-slate-800 dark:text-primary-100"
                 style="direction: rtl;">
                <div class="hidden md:flex items-center my-0 text-base font-bold divide-x divide-x-reverse gap-2"
                     style="line-height: 1.2; direction: rtl;">
                    <span class="px-1">{{ $t('message.state') }}</span>
                    <span v-if="$page.props.company?.name" class="px-1 ps-3">{{ $page.props.company?.name }}</span>
                    <span v-if="$page.props.auth?.department?.name" class="px-1 ps-3">
                        {{ $page.props.auth.department?.name}}
                    </span>
                    <span v-if="$page.props.auth?.section?.name" class="px-1 ps-3">{{ $page.props.auth.section.name }}</span>
                </div>
                <!--end::Heading-->
            </div>
            <div class="grow max-w-2xl px-8 lg:px-20" v-if="$page.props.allowSearch">
                <el-search-input/>
            </div>
            <div
                class="flex space-x-2 space-x-reverse shrink-0 justify-center items-center content-center leading-5 text-slate-900">
                <!-- <Popover ref="ref_popover" style="width: max-content" :breakpoints="{'960px': '75vw'}">
                    <UserMenu/>
                </Popover> -->
<!--                <MasterLayoutNotifications/>-->
                <div
                    class="flex space-x-2 space-x-reverse items-center bg-gray-50 dark:bg-slate-500/20 py-1 px-1.5 rounded-lg cursor-pointer text-slate-900 dark:text-primary-100"
                    @click="togglePopover">
                    <img :src="$page.props.auth.user?.avatar_url" class="h-8 w-8 rounded-full"/>
                    <span v-text="$page.props.auth.user?.name"/>
                    <i class="pi pi-chevron-down" style="font-size: 1rem"></i>
                </div>
            </div>
        </div>
    </section>
</template>

