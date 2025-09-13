<template>
    <component :is="href?'a':'span'" class="text-base" v-if="href"
               :class="{'text-primary-700 cursor-pointer hover:text-primary-500':href}"
               v-bind="$attrs" :href="href" style="white-space: nowrap;" :target="target">
        <ElText :value="label"/>
    </component>
    <ElText v-else/>
</template>

<script setup>
import ElText from "@/Components/Text/ElText.vue";

const props = defineProps({
    href: {type: String, default: null},
    label: {type: String, default: null},
    target: {type: String, default: '_parent'},
})
</script>

<style scoped>

</style>
