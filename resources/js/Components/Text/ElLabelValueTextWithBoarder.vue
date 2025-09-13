<template>
    <div>
        <p class="border-2 w-[150px] rounded-xl pt-2 h-[100px] content-center">
            {{ value !== null ? value?.toString() : '---' }}
            <br>
            {{ label }}
        </p>
    </div>
</template>

<script setup>

const props = defineProps({
    label: {type: String, default: null},
    value: {type: [String, Number], default: null},
})
</script>


<style scoped>

</style>
