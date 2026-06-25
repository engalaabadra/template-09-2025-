<template>

    <PageHead/>

    <div
        class="flex flex-col p-0 m-0 font-sans font-normal leading-5 box-border text-slate-900 print:overflow-scroll print:h-auto"
    >

        <div class="flex flex-col leading-5 box-border basis-auto text-slate-900">

            <!-- <ClientMenuBar/> -->

            <ScrollPanel style="width:100%;height: calc(-50px + 100vh )">
                <slot/>
            </ScrollPanel>

        </div>

    </div>

    <NotificationAlert/>
    <LoadingSection/>
</template>

<script setup>

import ScrollPanel from "primevue/scrollpanel";
import NotificationAlert from "@/Layout/Partial/NotificationAlert.vue";
import PageHead from "@/Layout/Partial/PageHead.vue";
//import ClientMenuBar from "@/Layout/Clients/ClientMenuBar.vue";
import LoadingSection from "@/Layout/Partial/LoadingSection.vue";
</script>
