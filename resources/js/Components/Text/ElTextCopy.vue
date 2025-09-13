<template>

    <span v-if="!isEmpty()" class="flex items-center gap-1">

        {{ label ?? value }}

        <span @click="copy(value,toast,t)" class="cursor-pointer">
            <i class="pi pi-clone"/>
        </span>
    </span>

    <span v-else-if="!hiddenEmpty" v-text="'---'"/>

</template>

<script setup>

import {copy} from "@/Helpers/Functions.js";
import {useI18n} from "vue-i18n";
import {useToast} from "primevue/usetoast";

const {t} = useI18n();
const toast = useToast();

const props = defineProps({
    label: {type: [String, Number], default: null},
    value: {type: [String, Number], default: null},
    zeroToNull: {type: Boolean, default: true},
    hiddenEmpty: {type: Boolean, default: false},
})

const isEmpty = () => {
    if ((props.value === '0' || props.value === 0) && props.zeroToNull) {
        return true;
    }

    return props.value == null || props.value === '';
}

</script>
