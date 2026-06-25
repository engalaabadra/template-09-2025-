<template>
    <aside class="floating-search-container"
           :class="{'floating-input-search-client':search_key||props.form[props.name]}">
        <FloatLabel>
            <InputText
                :required="required"
                v-model="search_key"
                @change="props.form[props.name] = null"
                v-bind="$attrs"
                class="w-full mt-0! px-[1.5rem]! py-[0.875rem]! h-[40px]"
                autocomplete="off"
                @focusin="drop_down_select.show = 1"
                @focusout="hide_drop_down_select"
            />

            <aside v-if="drop_down_select.show"
                   class="absolute mt-2 mr-[8px] max-w-[400px] overflow-auto rounded-md bg-white w-full z-10 py-1 shadow-xl shadow-black/5 ring-1 ring-slate-700/10">
                <template v-if="drop_down_select.el_data.length">
                    <p v-for="item in drop_down_select.el_data"
                       @click="view_selected(item);"
                       class="py-2 px-3 hover:bg-[#F5F5F5] cursor-pointer">{{ item.name }} : {{ item.national_id }}</p>
                </template>
                <template v-else-if="drop_down_select.loading">
                    <p class="py-2 px-3 hover:bg-[#F5F5F5] cursor-pointer">{{ $t('message.loading') }}</p>
                </template>
                <template v-else-if="search_key">
                    <p class="py-2 px-3 hover:bg-[#F5F5F5] cursor-pointer">{{ $t('message.no_data') }}</p>
                </template>
                <template v-else>
                    <p class="py-2 px-3 hover:bg-[#F5F5F5] cursor-pointer">
                        {{ $t('message.please_type_data_to_search') }}
                    </p>
                </template>
            </aside>

            <label>
                {{ label ?? $t('column.' + name) }}
                <ElTextRequired v-if="required"/>
            </label>
        </FloatLabel>
        <ElTextError v-if="hasError()" :value="form['errors'][name]"/>
    </aside>
</template>

<script setup>
import {ref, useAttrs, watch} from 'vue';
import {debounce} from 'lodash';
import InputText from 'primevue/inputtext';
import axios from 'axios';
import ElTextError from "@/Components/Text/ElTextError.vue";
import FloatLabel from "primevue/floatlabel";
import ElTextRequired from "@/Components/Text/ElTextRequired.vue";

// Props
const props = defineProps({
    search_text: {type: String, default: null},
    required: {type: Boolean, default: true},
    label: {type: String, default: null},
    form: Object,
    name: {type: String},
    url: {type: String},
});

// Refs and reactive data
const search_key = ref(props.search_text ?? '');
const drop_down_select = ref({
    show: 0,
    el_data: [],
    loading: 0,
});

// Methods
const hide_drop_down_select = () => {
    setTimeout(() => {
        drop_down_select.value.show = 0;
    }, 200);
};

const fetchDropdownData = debounce(() => {
    drop_down_select.value.el_data = [];
    drop_down_select.value.loading = 1;
    axios.post(`${props.url}?search=${search_key.value}`)
        .then(response => {
            drop_down_select.value.loading = 0;
            drop_down_select.value.el_data = response.data;
        });
}, 500);

const view_selected = (item) => {
    props.form[props.name] = item?.id;
    search_key.value = item['name'];
};
// Watchers
watch(search_key, (newVal) => {
    if (newVal) {
        fetchDropdownData();
    } else {
        props.form[props.name] = null;
        drop_down_select.value.el_data = [];
        drop_down_select.value.loading = 0
    }
});

// Use attrs for binding dynamic attributes
const $attrs = useAttrs();
const hasError = () => props.form && (props.form['errors'] ?? false) ? props.form['errors'][props.name] : !!props.customError;

</script>

<style scoped>
</style>
