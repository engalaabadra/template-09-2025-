<template>

    <section
        v-bind="$attrs"
        :class="{'border-e shadow-sm':useBorder}"
        class="md:w-[15rem] md:flex leading-5 z-50 relative lg:fixed lg:inset-y-0 lg:start-0 el-container"
    >
        <div class="flex flex-col w-full">
            <div class="flex-col flex-none items-center pb-5 pt-2 flex">
                <a
                    class="text-primary-500 cursor-pointer box-border hover:text-yellow-700"
                    style="text-decoration: none; transition: color 0.2s ease 0s, background-color 0.2s ease 0s; direction: rtl;"
                >
                    <img
                        alt="Logo"
                        :src="asset('images/logo-full.png')"
                        class="m-auto w-auto px-2 align-middle box-border max-h-[70px]"
                    />
                </a>
            </div>
            <div
                class="flex flex-col px-2 grow shrink-0 pb-5 items-center w-full basis-auto h-full menu-container"
            >
                <ScrollPanel
                    style="width:100%;height: calc(-180px + 100vh )"
                    class="p-scrollpanel-bar-y aside-scroll-panel relative"
                >
                    <ul class="w-full divide-y divide-slate-100 dark:divide-slate-700 font-bold h-[85vh] overflow-y-auto">
                        <li v-for="item in $page.props.menu" class="py-2 leading-5 box-border">
                            <Link
                                :href="item.href"
                                class="flex cursor-pointer justify-center items-center"
                                :class="{
                                    'text-primary-600 active':item.active,
                                    'text-slate-600 dark:text-white': !item.active
                                }"
                            >
                                <label class="block cursor-pointer text-center">
                                    <i
                                        class="pi text-lg align-middle py-1 px-1.5"
                                        :class="item.icon"
                                    />
                                </label>
                                <label class="text-xs md:text-sm cursor-pointer w-full" v-text="$t(item.label)"/>
                            </Link>
                        </li>
                    </ul>
                </ScrollPanel>
            </div>
        </div>
    </section>

</template>

<script setup>

import ScrollPanel from "primevue/scrollpanel";
import {asset} from "@/Helpers/Functions.js";
import {ref} from "vue";

const props = defineProps({
    useBorder: {type: Boolean, default: 1},
})

const visible = ref(true);

const toggle_aside = () => {
    visible.value = !visible.value;
}

defineExpose({toggle_aside});

</script>
