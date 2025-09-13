<template>
    <Toast/>
    <Toast position="top-left" group="tl">
        <template #message="slotProps">
            <div @click="clickToast(slotProps)" class="w-full">
                <component :is="slotProps?.message?.link_href?Link:'div'" :href="slotProps?.message?.link_href"
                           class="flex flex-grow">
                    <div v-if="slotProps.message?.detail" v-html="slotProps.message?.detail"/>
                </component>
            </div>
        </template>
    </Toast>
</template>

<script setup>
import Toast from 'primevue/toast';
import {Link, useForm, usePage} from "@inertiajs/vue3";
import ElCreatedByText from "@/Components/Text/ElCreatedByText.vue";
import {onMounted, watch} from "vue";
import {useToast} from "primevue/usetoast";
import {printContent} from "@/Helpers/Functions.js";
import {useI18n} from "vue-i18n";

const {t} = useI18n();

const toast = useToast();

const _page = usePage();

const clickToast = (slotProps) => {
    if (slotProps?.message?.mark_as_read_url) {
        useForm().post(slotProps?.message?.mark_as_read_url);
    }
};

watch(
    () => _page.props.toastr,
    (toastr) => {
        if (toastr != null) {
            for (let i = 0; i < toastr.length; i++) {
                toast.add({
                    severity: toastr[i]?.type,
                    detail: toastr[i]?.make_translate ? t(toastr[i]?.message) : toastr[i]?.message,
                    life: 6000,
                });
            }
        }
    },
    {deep: true}
);

watch(
    () => _page.props.errors,
    (errors) => {
        if (errors != null) {
            let i = 0;
            for (let item in errors) {
                if (i === 0) {
                    toast.add({
                        severity: 'error',
                        detail: t('message.please_check_data'),
                        life: 5000,
                    });
                    toast.add({
                        severity: 'error',
                        detail: errors[item],
                        life: 5000 + i * 1000,
                    });
                }
                i++;
            }
        }
    },
    {deep: true}
);

// Mounted check for cant_access
onMounted(() => {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('cant_access') && urlParams.get('cant_access') !== false) {
        toast.add({
            severity: 'error',
            detail: t('message.cant_access'),
            life: 5000,
        });
        setTimeout(function () {
            const params = new URLSearchParams(window.location.search);
            if (params.has('cant_access')) {
                window.location.href = window.location.toString().replace('cant_access=' + params.get('cant_access'), '');
            }
        }, 3000);
    }
});

</script>
