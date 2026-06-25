<script setup>
const props = defineProps({
    count: {type: Number, default: null},
    icon: {type: String, default: null},
});
</script>

<template>
    <aside class="bg-gray-50 dark:bg-slate-500/20 cursor-pointer inline-flex relative
            justify-center items-center p-0 w-10 h-10 text-lg font-medium text-center rounded-lg"
           :class="{'me-4':count>0,'me-2':!count}">
        <i :class="icon" class=" text-primary-500 dark:text-primary-300"></i>
        <label v-if="count > 0"
               class="bg-red-600 text-white pt-1 block absolute -top-2 -left-3 px-1 place-content-center aspect-square rounded-full">
            <label class="block p-0.5 text-sm leading-[0]!">
                {{
                    count > 99 ? 99 : count
                }}
            </label>
        </label>
    </aside>
</template>

<style scoped>

</style>
