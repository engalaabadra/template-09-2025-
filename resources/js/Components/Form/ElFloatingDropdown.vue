<template>

    <div>

        <FloatLabel :variant="variant">

            <!-- <Select
                :options="options"
                :optionLabel="optionLabel"
                :option-value="optionValue"
                class="w-full"
                filter
                :show-clear="clearable"
                :disabled="disabled || form.processing"
                v-bind="$attrs"
                :class="{ 'p-invalid': hasError() }"
                v-model="form[name]"
            /> -->

            <label v-if="showLabel">
                {{ label ?? $t('column.' + name) }}
                <ElTextRequired v-if="required"/>
            </label>

        </FloatLabel>

        <ElTextError v-if="hasError()" :value="form['errors'][name]"/>

    </div>

</template>

<script setup>

import FloatLabel from "primevue/floatlabel";
import ElTextError from "@/Components/Text/ElTextError.vue";
//import Select from 'primevue/select';
import ElTextRequired from "@/Components/Text/ElTextRequired.vue";

const props = defineProps({
    label: String,
    name: String,
    options: {type: [Object, Array], default: null},
    optionLabel: {type: String, default: 'name'},
    optionValue: {type: String, default: 'id'},
    form: Object,
    required: {
        type: Boolean,
        default: false
    },
    clearable: {
        type: Boolean,
        default: true
    },
    showLabel: {
        type: Boolean,
        default: true
    },
    variant: {
        type: String,
        default: 'on'
    },
    disabled: {
        type: Boolean,
        default: false
    },
})

const hasError = () => props.form && (props.form['errors'] ?? false) ? props.form['errors'][props.name] : false;

</script>
