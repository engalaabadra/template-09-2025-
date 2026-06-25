<template>
    <section class="inline-block" v-bind="$attrs">
        <div class="flex gap-1" v-if="model && model.id">
             <span v-if="viewProject && model.project">
                {{model.project.name_text2}}
                <span class="mx-0.5"> - </span>
            </span>
            <span>
                {{ model.name }}
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
