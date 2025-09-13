<template>
    <div class="flex items-center gap-1" v-if="value || !hideEmpty">
        <span v-if="label">{{ label }} : </span>
        <div v-if="!$slots.value">
            <AvatarProfile :url="url"/>
        </div>
        <slot name="value"/>
    </div>
</template>

<script setup>
import AvatarProfile from "@/Components/Avatar/AvatarProfile.vue";

const props = defineProps({
    label: {type: String, default: null},
    url: {type: [String, Number], default: null},
})
</script>


<style scoped>

</style>
