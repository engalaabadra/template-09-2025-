<template>
    <span v-if="!isEmpty()">
        <component :is="href ? 'a' : 'label'" :href="href" :class="{'link-text':href}" target="_blank">
            {{ value }}
            <span v-if="append">{{ append }}</span>
        </component>
    </span>
    <span v-else-if="!hiddenEmpty">
        ---
    </span>
</template>

<script setup>
const props = defineProps({
    value: {type: [String, Number], default: null},
    href: {type: String, default: null},
    append: {type: String, default: null},
    zeroToNull: {type: Boolean, default: true},
    hiddenEmpty: {type: Boolean, default: false}
})

const isEmpty = () => {
    if (!props.href)
        return true;
    if ((props.value === '0' || props.value === 0) && props.zeroToNull)
        return true;

    return props.value == null || props.value === '';
}
</script>

<style scoped>

</style>
