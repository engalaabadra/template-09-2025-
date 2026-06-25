<template>
    <aside>
        <FloatLabel :variant="variant">
            <TreeSelect selectionMode="single" :options="options" filter
                        class="w-full" :show-clear="clearable" :disabled="form.processing"
                        :class="{'p-invalid':hasError()}" v-model="model_value"
                        :optionLabel="optionLabel" :option-value="optionValue"/>
            <label v-if="showLabel">
                {{ label ?? $t('column.' + name) }}
                <ElTextRequired v-if="required"/>
            </label>
        </FloatLabel>
        <ElTextError v-if="hasError()" :value="form['errors'][name]"/>
    </aside>
</template>

<script setup>
import FloatLabel from "primevue/floatlabel";
import ElTextError from "@/Components/Text/ElTextError.vue";
import ElTextRequired from "@/Components/Text/ElTextRequired.vue";
import TreeSelect from "primevue/treeselect";
import {ref, watch, defineEmits} from "vue";

const emit = defineEmits(["change"])
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
})
const model_value = ref();

const setCurrentValue = () => {
    let form_value = props.form[props.name];
    if (model_value.value || !form_value) {
        return;
    }
    let tree_object = {};
    tree_object[form_value] = true;
    model_value.value = tree_object;
}
setCurrentValue();
watch(props.form, (newValue) => {
    setCurrentValue();
});
watch(model_value, (newValue) => {
    props.form[props.name] = newValue?Object.keys(newValue)?.[0]:null;
    emit("change", newValue)
});
const hasError = () => props.form && (props.form['errors'] ?? false) ? props.form['errors'][props.name] : false;
</script>

<style scoped>

</style>
