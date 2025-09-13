<template>
    <ElLabelValueText :value="data[name]" :label="$t('message.'+name)"/>
</template>

<script setup>
import ElLabelValueText from "@/Components/Text/ElLabelValueText.vue";

const props = defineProps({
    data: {type: Object, default: null},
    name: {type: String, default: null},
})
</script>


<style scoped>

</style>
