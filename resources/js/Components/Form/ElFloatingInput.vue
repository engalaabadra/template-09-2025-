<template>
   <aside>
       <FloatLabel :variant="variant">
           <InputText :readonly="readonly" :disabled="processing || form.processing || disabled" v-if="form && name" :type="type" :class="{'p-invalid':hasError()}" v-model="form[name]" v-bind="$attrs"/>
           <InputText v-else :value="modelValue" @input="updateValue" :type="type" :class="{'p-invalid': hasError(), 'p-filled': modelValue}" v-bind="$attrs"/>
           <label v-if="showLabel">
               {{ label ?? $t('column.' + name) }}
               <ElTextRequired v-if="required"/>
           </label>
       </FloatLabel>
       <ElTextError v-if="hasError()" :value="customError??form['errors'][name]"/>
       <ElTextWarning v-if="warningText" :value="warningText"/>
   </aside>
</template>

<script setup>
import InputText from 'primevue/inputtext';
import ElTextRequired from "@/Components/Text/ElTextRequired.vue";
import ElTextError from "@/Components/Text/ElTextError.vue";
import FloatLabel from 'primevue/floatlabel';
import ElTextWarning from "@/Components/Text/ElTextWarning.vue";

const props = defineProps({
    label: String,
    name: String,
    type: {
        type: String,
        default: 'text'
    },
    form: Object,
    readonly: {
        type: Boolean,
        default: false
    },
    required: {
        type: Boolean,
        default: false
    },
    variant: {
        type: String,
        default: 'on'
    },
    modelValue: {
        type: String,
        default: null
    },
    customError:{
        type:Object,
        default:null,
    },
    processing: {
        type: Boolean,
        default: false
    },
    disabled: {
        type: Boolean,
        default: false
    },
    showLabel: {
        type: Boolean,
        default: true
    },
    warningText: {
        type: String,
        default: null
    },
})

const hasError = () => props.form && (props.form['errors'] ?? false) ? props.form['errors'][props.name] : !!props.customError;
const emit = defineEmits(['update:modelValue'])
const updateValue = (event) => {
    emit('update:modelValue', event.target.value)
}
</script>

<style scoped>
</style>
