<template>
    <section class="inline-block" v-bind="$attrs">
        <div class="flex gap-1" v-if="model && model.id">
            <span>
                {{ model.name }}
            </span>
            <span v-if="viewBuilding && model.building">
                <span class="mx-0.5"> - </span>
                {{model.building.name}}
            </span>
            <span v-if="viewProject && model.project">
                <span class="mx-0.5"> - </span>
                {{model.project.name_text2}}
            </span>
        </div>
    </section>
</template>

<script setup>
const props = defineProps({
    model: {type: Object, default: null},
    viewBuilding: {type: Boolean, default: true},
    viewProject: {type: Boolean, default: true},
})
</script>

<style scoped>

</style>
