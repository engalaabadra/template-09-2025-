<template>
    <Dialog v-model:visible="ref_view_dialog" :style="{width: '40rem'}" modal maximizable
            :header="$t('message.chat')">
        <form @submit.prevent="submitNewChat" class="grid grid-cols-1 gap-5 mt-3">
            <div class="flex flex-col gap-3">
                <template v-if="!el_to_model_name">
                    <ElFloatingDropdown :form="el_form" required name="to_type" :label="$t('column.to_model_type')"
                                        :options="form_data.to_model_types"/>
                    <div v-if="el_form.to_type">
                        <SearchClientAutoComplete v-if="el_form.to_type==='client_id'"
                                                  required :form="el_form" name="to_model_id"/>
                        <ElFloatingDropdown v-else required :form="el_form" option-label="name2"
                                            :options="form_data.users[el_form.to_type]" name="to_model_id"/>
                    </div>
                </template>
                <div v-else class="flex gap-2">
                    <span>{{ $t('message.send_message') }} {{ $t('message.to') }}</span>
                    <span class="underline font-bold" v-text="el_to_model_name"/>
                </div>
                <ElFormInputSwitch :form="el_form" name="allow_whatsapp"/>
                <ElFloatingTextarea required :form="el_form" name="message"/>
            </div>
            <div class="flex justify-end gap-4">
                <ElSecondaryButton :text="$t('message.cancel')" @click="ref_view_dialog=false"/>
                <ElSubmitButton :text="$t('message.send_message')" :form="el_form"/>
            </div>
        </form>
    </Dialog>
</template>

<script setup>
import Dialog from "primevue/dialog";
import {ref} from "vue";
import ElFloatingDropdown from "@/Components/Form/ElFloatingDropdown.vue";
import {useForm} from "@inertiajs/vue3";
import {useI18n} from "vue-i18n";
import SearchClientAutoComplete from "@/Components/Form/SearchClientAutoComplete.vue";
import ElFormInputSwitch from "@/Components/Form/ElFormInputSwitch.vue";
import ElSubmitButton from "@/Components/Buttons/ElSubmitButton.vue";
import ElSecondaryButton from "@/Components/Buttons/ElSecondaryButton.vue";
import ElFloatingTextarea from "@/Components/Form/ElFloatingTextarea.vue";
import {Enum} from "@/enum.js";

const ref_view_dialog = ref(false);
const {t} = useI18n()
const el_to_model_name = ref(null);
const showDialog = (model_type = null, model_id = null, to_model_name = null) => {
    el_form.reset();
    el_form.to_model_type = model_type;
    el_form.to_model_id = model_id;
    el_to_model_name.value = to_model_name;

    ref_view_dialog.value = true;
}

let model_type_user = 'App\\Models\\User';
let model_type_client = 'App\\Models\\Client';

const emit = defineEmits(['success'])

const el_form = useForm({
    'to_type': null,
    'to_model_type': null,
    'to_model_id': null,
    'allow_whatsapp': false,
    'open_platform': Enum.ChatOpenPlatformEnum.DASHBOARD,
    'message': null,
})
const submitNewChat = () => {
    el_form.to_model_type = el_form.to_type === 'client_id' ? model_type_client : model_type_user;
    el_form.post(route('chat.store'), {
            onSuccess: (response) => {
                el_form.reset();
                ref_view_dialog.value = false;
                emit('success');
                window.dispatchEvent(new CustomEvent('open-chat-drawer', {
                    detail: {
                        newChat: true
                    }
                }));
            },
        }
    );
}

const form_data = ref();

const getFormData = () => {
    form_data.value = [];
    axios.post(route('chat.get-form-data')).then(response => {
        form_data.value = response.data.form_data;
    });
}
getFormData();
defineExpose({
    showDialog
});
</script>
