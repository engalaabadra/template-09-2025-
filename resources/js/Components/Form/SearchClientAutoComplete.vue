<template>
    <aside class="floating-search-container"
           :class="{'floating-input-search-client':search_key||props.form[props.name]}">
        <FloatLabel>
            <InputText
                :required="required"
                v-model="search_key"
                @change="props.inChangeEmptySetNull?props.form[props.name] = null:null"
                v-bind="$attrs"
                autocomplete="off"
                @focusin="drop_down_select_client.show = 1"
                @focusout="hide_drop_down_select_client"
                :id="id"
        />

            <aside
                v-if="drop_down_select_client.show"
                class="absolute flex flex-col mt-1 max-w-[400px] overflow-auto rounded-md border bg-white w-full z-10 py-1 shadow-xl"
            >

                <span
                    v-if="drop_down_select_client.clients.length"
                    v-for="item in drop_down_select_client.clients"
                    @click="viewSelected(item)"
                    class="py-2 px-3 hover:bg-[#F5F5F5] cursor-pointer"
                    v-text="item.name + ' : ' + item.national_id"
                />

                <span
                    v-else-if="drop_down_select_client.loading"
                    class="py-2 px-3 hover:bg-[#F5F5F5] cursor-pointer"
                    v-text="$t('message.loading')"
                />

                <span
                    v-else-if="search_key"
                    class="py-2 px-3 hover:bg-[#F5F5F5] cursor-pointer"
                    v-text="$t('message.no_data')"
                />

                <span
                    v-else
                    class="py-2 px-3 hover:bg-[#F5F5F5] cursor-pointer"
                    v-text="$t('message.please_type_data_to_search')"
                />

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
import {ref, watch} from 'vue';
import {debounce} from 'lodash';
import InputText from 'primevue/inputtext';
import axios from 'axios';
import ElTextError from "@/Components/Text/ElTextError.vue";
import FloatLabel from "primevue/floatlabel";
import ElTextRequired from "@/Components/Text/ElTextRequired.vue";

const props = defineProps({
    search_text: {type: String, default: null},
    required: {type: Boolean, default: true},
    label: {type: String, default: null},
    form: Object,
    name: {type: String},
    inChangeEmptySetNull: {
        type: Boolean,
        default: true,
    },
    id: {
        type: String,
        default: null,
    },
});

const search_key = ref(props.search_text ?? null);
const drop_down_select_client = ref({
    show: 0,
    clients: [],
    loading: 0,
});

const hide_drop_down_select_client = () => {
    setTimeout(() => {
        drop_down_select_client.value.show = 0;
    }, 200);
};

const fetchClients = debounce(() => {
   searchForClients()
}, 500);
const searchForClients = () => {
    drop_down_select_client.value.clients = [];
    drop_down_select_client.value.loading = 1;
    let current_selected_value = props.form[props.name];
    if (current_selected_value)
        current_selected_value = '#' + current_selected_value;
    axios.post(route('dashboard.clients.search-client'), {
        search: search_key.value ?? current_selected_value,
    })
        .then(response => {
            drop_down_select_client.value.loading = 0;
            drop_down_select_client.value.clients = response.data;
            if (response.data.length === 1)
                viewSelected(response.data[0]);
        });
}
if (props.form[props.name])
    searchForClients();
const emit = defineEmits(['selectClient','inputKeyup']);

const viewSelected = (item) => {
    props.form[props.name] = item?.id;
    search_key.value = item['name'];
    emit('selectClient', item);
};

const changeInputValue = () => {
    emit('inputKeyup', search_key.value);
}

watch(search_key, (newVal) => {
    changeInputValue();
    if (newVal) {
        fetchClients();
    } else {
        props.form[props.name] = null;
        drop_down_select_client.value.clients = [];
        drop_down_select_client.value.loading = 0
    }
});

const hasError = () => props.form && (props.form['errors'] ?? false) ? props.form['errors'][props.name] : !!props.customError;

</script>
