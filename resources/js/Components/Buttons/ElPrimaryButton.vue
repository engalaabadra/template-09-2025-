<template>

    <component
        :is="href ? (isA ? 'a' : 'Link') : 'button'"
        v-bind="$attrs" :href="href"
        :class="form?.processing || progressing ? '!cursor-progress' : (disabled ? '!cursor-default' : '')"
        :disabled="disabled || form?.processing || progressing"
        class="bg-primary-500 border-primary-600 hover:bg-primary-400 text-white button-classes"
    >

        <span v-if="text" v-text="text"/>

        <slot v-else/>

    </component>

</template>

<script setup>


const props = defineProps({
    text: {
        type: String,
        default: null,
    },
    href: {
        type: String,
        default: null,
    },
    ability: {
        type: String,
        default: null,
    },
    isA: {
        type: Boolean,
        default: false,
    },
    disabled: {
        type: Boolean,
        default: false
    },
    form: {type: Object, default: {}},
    progressing: {
        type: Boolean,
        default: false
    },
})

</script>
