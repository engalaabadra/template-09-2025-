<template>
    <section class="inline-block" v-bind="$attrs">
        <div class="flex gap-1" v-if="model && model.id">
            <span v-if="model.target_type === 'unit'">
                {{ model.unit?.name_full }}
            </span>
            <span v-else-if="model.target_type === 'building'">
                {{ model.building?.name }}
            </span>
            <span v-if="viewProject && model.project">
                <span class="mx-0.5"> - </span>
                {{model.project.name_text}}
            </span>
        </div>
    </section>
</template>

<script setup>
const props = defineProps({
    model: {type: Object, default: null},
    viewProject: {type: Boolean, default: true},
})
</script>

<style scoped>

</style>
