<template>

    <button
        class="bg-primary-500 border border-primary-500 disabled:opacity-80 text-white py-2 px-8 rounded hover:bg-opacity-80 !w-auto transition-all duration-300 text-base font-bold"
        type="submit"
        :class="form?.processing || progressing ? '!cursor-progress' : (disabled ? '!cursor-default' : '')"
        :disabled="disabled || form?.processing || progressing"
        v-bind="$attrs"
    >

        <el-loading-dots v-if="progressing || form?.processing"/>

        <span v-else v-text="text"/>

    </button>

</template>

<script setup>


defineProps({
    progressing: {
        type: Boolean,
        default: false
    },
    disabled: {
        type: Boolean,
        default: false
    },
    form: {type: Object, default: {}},
    text: String
})

</script>
