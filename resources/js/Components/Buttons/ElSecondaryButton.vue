<template>

    <component :is="href ? 'Link' : 'button'" type="button"
               :href="href" v-bind="$attrs"
               :class="form?.processing || progressing ? '!cursor-progress' : (disabled ? '!cursor-default' : '')"
               :disabled="disabled || form?.processing || progressing"
               class="bg-white border-secondary-500 text-secondary-500 hover:bg-secondary-500 hover:text-white button-classes"
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
