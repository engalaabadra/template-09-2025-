<template>
    <Breadcrumb v-if="$page.props.breadcrumb?.length" class="bg-transparent! dark:text-white border-0 text-base p-0"
                :model="$page.props.breadcrumb">
        <!-- <template #item="{item}">
            <ElAbilityLink :href="item.url" :ability="item?.ability">
                <label class="p-menuitem-icon text-sm" :class="item.icon"></label>
                {{ item.label }}
            </ElAbilityLink>
        </template> -->
    </Breadcrumb>
</template>

<script setup>
import Breadcrumb from "primevue/breadcrumb";
//import ElAbilityLink from "@/Components/Ability/ElAbilityLink.vue";
</script>


<style scoped>

</style>
