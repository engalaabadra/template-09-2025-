<template>
    <span class="whitespace-nowrap">
        <span v-if="value" class="flex items-center gap-2">
             <span class="flex items-center hover:underline gap-1">
                 <a :href="'tel:' + formattedValue" target="_blank" dir="ltr">
                    {{ value }}
                 </a>
            </span>
            <span class="flex items-center gap-1">
                <a :href="'https://wa.me/' + whatsappNumber" class="hover:underline" target="_blank">
                    <i class="pi pi-whatsapp text-green-400" style="font-size: .8rem"></i>
                </a>
            </span>
        </span>
        <ElText v-else/>
    </span>
</template>

<script setup>
import {computed} from "vue";

const props = defineProps({
    value: [String, Number],
});
const formattedValue = computed(() => {
    let number = String(props.value || '');
    // Remove any non-digit character
    number = number.replace(/\D/g, '');
    // Add +966 if not already present
    if (!number.startsWith('966')) {
        number = '966' + number.replace(/^0+/, ''); // Remove leading zero if present
    }
    return '+' + number;
});

const whatsappNumber = computed(() => {
    let number = String(props.value || '');
    number = number.replace(/\D/g, '');
    if (!number.startsWith('966')) {
        number = '966' + number.replace(/^0+/, '');
    }
    return number;
});

</script>
