<template>
    <a @click="localeUpdate" class="dark:bg-gray-700 cursor-pointer flex gap-2 bg-slate-100 py-1 w-[80px] px-3 rounded-sm">
        <img :src="englishLocale ? asset('images/flags/sa.png') : asset('images/flags/us.png')" alt="Flag" class="w-6"/>
        <span v-text="englishLocale ? 'AR' : 'EN'"/>
    </a>
</template>

<script setup>
import {router, usePage} from "@inertiajs/vue3";
import {asset} from "@/Helpers/Functions.js";
import {collect} from "collect.js";

const englishLocale = usePage().props.lang.current === "en";
const props_locals = usePage().props.lang?.locals;

const localeUpdate = () => {
    window.location.href = collect(props_locals).where('id',englishLocale?'ar':'en').first().url;

};

</script>
