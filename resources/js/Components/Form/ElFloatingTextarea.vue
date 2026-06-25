<template>

    <aside>

        <FloatLabel :variant="variant">

            <Textarea
                :readonly="readonly"
                v-model="form[name]"
                :id="name"
                class="w-full"
                :disabled="processing || form.processing"
                :rows="rows?? '10'"
                :cols="cols ?? '30'"
                :class="{ 'p-invalid': hasError() }"
                v-bind="$attrs"
                autoResize
            />

            <label>
                {{ label ?? $t('column.' + name) }}
                <ElTextRequired v-if="required"/>
            </label>

        </FloatLabel>

        <ElTextError
            v-if="hasError()"
            :value="customError ?? form['errors'][name]"
        />

    </aside>

</template>

<script setup>

import Textarea from 'primevue/textarea';
import ElTextError from "@/Components/Text/ElTextError.vue";
import ElTextRequired from "@/Components/Text/ElTextRequired.vue";
import FloatLabel from "primevue/floatlabel";
``
const props = defineProps({
    label: String,
    name: String,
    form: Object,
    rows: {type: Number, default: 2},
    cols: {type: Number, default: 100},
    required: {type: Boolean, default: false},
    customError: {
        type: Object,
        default: null,
    },
    readonly: {
        type: Boolean,
        default: false
    },
    processing: {
        type: Boolean,
        default: false
    },
    variant: {
        type: String,
        default: 'on'
    },
});

const hasError = () => props.form && (props.form['errors'] ?? false) ? props.form['errors'][props.name] : !!props.customError;

</script>
