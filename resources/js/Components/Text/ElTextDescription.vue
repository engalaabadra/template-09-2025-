<template>

    <div v-if="!isEmpty()">

        <div class="max-w-[250px] w-[250px] max-h-[50px] overflow-auto whitespace-normal"
            v-html="value"/>


        <span v-if="append">{{ append }}</span>

    </div>

    <div v-else-if="!hiddenEmpty" v-text="'---'"/>

</template>

<script setup>

const props = defineProps({
    value: {type: [String, Number], default: null},
    append: {type: String, default: null},
    zeroToNull: {type: Boolean, default: true},
    hiddenEmpty: {type: Boolean, default: false}
})

const isEmpty = () => {
    if ((props.value === '0' || props.value === 0) && props.zeroToNull) {
        return true;
    }

    return props.value == null || props.value === '';
}

</script>
