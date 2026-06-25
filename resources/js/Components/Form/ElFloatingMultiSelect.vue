<template>

    <div class="multliselect grow">

        <FloatLabel>

            <MultiSelect
                :options="options"
                :optionLabel="optionLabel"
                :optionValue="optionValue"
                class="w-full"
                filter
                show-clear
                :disabled="form.processing"
                v-bind="$attrs"
                :class="{'p-invalid':hasError()}"
                v-model="form[name]"
            />

            <label>
                {{ label ?? $t('column.' + name) }}
                <SpanRequired v-if="required"/>
            </label>

        </FloatLabel>

        <ElTextError
            v-if="hasError()"
            :value="form['errors'][name]"
        />

    </div>

</template>

<script setup>

import SpanRequired from "@/Components/Form/ElSpanRequired.vue";
import FloatLabel from "primevue/floatlabel";
import ElTextError from "@/Components/Text/ElTextError.vue";
import MultiSelect from 'primevue/multiselect';

const props = defineProps({
    label: String,
    name: String,
    options: {type: Object, default: null},
    optionLabel: {type: String, default: 'name'},
    optionValue: {type: String, default: 'id'},
    form: Object,
    required: {
        type: Boolean,
        default: false
    },
})

const hasError = () => props.form && (props.form['errors'] ?? false) ? props.form['errors'][props.name] : false;

</script>
