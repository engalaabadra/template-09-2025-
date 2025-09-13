<template>
    <Dialog v-model:visible="ref_view_dialog" :style="{width: '40rem'}" modal maximizable
            :header="$t('message.transfer_chat')">
        <form @submit.prevent="submitTransferChat" class="grid grid-cols-1 gap-5 mt-3">
            <div class="flex flex-col gap-3">
                <ElFloatingDropdown required :form="el_form" option-label="name2"
                                    :options="form_data.users" name="user_id"/>
            </div>
            <div class="flex justify-end gap-4">
                <ElSecondaryButton :text="$t('message.cancel')" @click="ref_view_dialog=false"/>
                <ElSubmitButton :text="$t('message.move')" :form="el_form"/>
            </div>
        </form>
    </Dialog>
</template>

<script setup>
import Dialog from "primevue/dialog";
import {ref} from "vue";
import ElFloatingDropdown from "@/Components/Form/ElFloatingDropdown.vue";
import {useForm} from "@inertiajs/vue3";
import ElSubmitButton from "@/Components/Buttons/ElSubmitButton.vue";
import ElSecondaryButton from "@/Components/Buttons/ElSecondaryButton.vue";

const ref_view_dialog = ref(false);

const el_form = useForm({
    'user_id': null,
    'chat_id': null,
})
const showDialog = (el_chat) => {
    el_form.chat_id = el_chat.id;
    ref_view_dialog.value = true;
}

const emit = defineEmits(['success'])

const submitTransferChat = () => {
    el_form.post(route('chat.move-chat',el_form.chat_id), {
            onSuccess: () => {
                ref_view_dialog.value = false;
                emit('success');
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
