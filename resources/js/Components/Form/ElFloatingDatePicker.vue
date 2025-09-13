<template>

    <aside class="w-full">

        <FloatLabel :variant="variant">

            <!-- <DatePicker
                v-if="form && name"
                v-model="form[name]"
                :manualInput="false"
                iconDisplay="input"
                selectionMode="single"
                dateFormat="yy/mm/dd"
                showButtonBar
                showIcon :disabled="form.processing"
                fluid
                @update:model-value="formatDate"
                class="w-full"
                :class="{'p-invalid':hasError()}"
                v-bind="$attrs"
                pt:title="order-none! w-full! justify-center!"
            />

            <DatePicker
                v-else
                v-model="value"
                :manualInput="false"
                iconDisplay="input"
                dateFormat="yy/mm/dd"
                showButtonBar
                showIcon
                fluid
                @update:model-value="formatDate"
                class="w-full"
                :class="{'p-invalid':hasError()}"
                v-bind="$attrs"
                pt:title="order-none! w-full! justify-center!"
            /> -->

            <label v-if="showLabel">
                {{ label ?? $t('column.' + name) }}
                <SpanRequired v-if="required"/>
            </label>

        </FloatLabel>

        <ElTextError
            v-if="hasError()"
            :value="form['errors'][name]"
        />

    </aside>

</template>

<script setup>

import SpanRequired from "@/Components/Form/ElSpanRequired.vue";
import FloatLabel from "primevue/floatlabel";
import ElTextError from "@/Components/Text/ElTextError.vue";
//import DatePicker from 'primevue/datepicker';
import {ref} from 'vue';

const props = defineProps({
    modelValue: [String, Object],
    label: String,
    name: String,
    form: Object,
    required: {
        type: Boolean,
        default: false
    },
    showLabel: {
        type: Boolean,
        default: true
    },
    variant: {
        type: String,
        default: 'on'
    },
})

let value = ref(props.modelValue);
const emit = defineEmits(['change']);
const formatDate = (value) => {
    if (!value) {
        // If the value is null or empty, clear the date
        if (props.form && props.name) {
            props.form[props.name] = null;
        }
        props.modelValue = null;
    } else {
        if (Array.isArray(value)) {
            for (let i = 0; i < value.length; i++) {
                let date = new Date(value[i]);
                const offset = date.getTimezoneOffset();
                date = new Date(date.getTime() - (offset * 60 * 1000));
                if (props.modelValue && props.modelValue[i])
                    props.modelValue[i] = date.toISOString().split('T')[0];
            }
        } else {
            // Format the date as before
            let date = new Date(value);
            const offset = date.getTimezoneOffset();
            date = new Date(date.getTime() - (offset * 60 * 1000));
            const formattedDate = date.toISOString().split('T')[0];

            if (props.form && props.name) {
                props.form[props.name] = formattedDate;
            } else {
                // Update the value ref for the second DatePicker
                value.value = formattedDate;
            }
        }
    }
    emit('change', props.form && props.name ? props.form[props.name] : value.value);
}

const hasError = () => props.form && (props.form['errors'] ?? false) ? props.form['errors'][props.name] : false;

</script>
