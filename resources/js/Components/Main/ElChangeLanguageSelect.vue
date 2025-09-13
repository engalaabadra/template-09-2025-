<script setup>
import {ref} from 'vue';
import {usePage} from "@inertiajs/vue3";
import {collect} from "collect.js";
import Select from 'primevue/select';

const props_locals = usePage().props.lang?.locals;
const selected_lang = ref(collect(props_locals).where('id',usePage().props.lang?.current).first());
const changeLang = () => {
    window.location.href = collect(props_locals).where('id', selected_lang.value.id).first()?.url;
}
</script>

<template>
    <Select v-model="selected_lang" @change="changeLang"
            :options="props_locals" option-label="name" class="select-change-lang h-[37px] w-[140px] ltr:w-[140px]">
        <template #value="slotProps">
            <div class="flex items-center gap-2">
                <img :alt="slotProps.value.name"
                     :src="slotProps.value.img_src"
                     style="width: 22px"/>
                <div>{{ slotProps.value.name }}</div>
            </div>
        </template>
        <template #option="slotProps">
            <div class="flex items-center gap-2">
                <img :alt="slotProps.option.name"
                     :src="slotProps.option.img_src"
                      style="width: 22px"/>
                <div>{{ slotProps.option.name }}</div>
            </div>
        </template>
    </Select>

</template>

<style>
.select-change-lang .p-select-label{
    padding-top: 6px!important;
}
</style>
