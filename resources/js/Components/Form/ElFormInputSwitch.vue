<template>
    <aside v-if="form && name" >
        <Label class="flex cursor-pointer items-center">
            <!-- <ToggleSwitch :disabled="form.processing" :id="id" v-model="form[name]"
                          :class="{'p-invalid':hasError()}"/> -->
            <span class="ms-2 inline-block pt-1.5 select-none" v-if="!hideLabel">
                 {{ label ?? $t('column.' + name) }}
             </span>
        </Label>
    </aside>
</template>

<script setup>

//import ToggleSwitch from "primevue/toggleswitch";

const props = defineProps({
    name: {
        type: String,
        required: true,
    },
    form: {
        type: Object,
        required: true,
    },
    label: String,
    hideLabel: {
        type: Boolean,
        default: false,
    },
})

const hasError = () => props.form && (props.form['errors'] ?? false) ? props.form['errors'][props.name] : false;
</script>

<style scoped>

</style>
