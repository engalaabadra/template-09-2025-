<template>
    <section class="loading-section-class hidden">
        <div class="fixed top-0 left-0 right-0 bottom-0 z-[10000] flex justify-center items-center text-white text-3xl" style="background-color: rgba(0, 0, 0, 0.3)">
            {{$t('message.loading_please_wait')}}
        </div>
    </section>
</template>

<script setup></script>
<style scoped>
.is-loading .loading-section-class{
    display: block!important;
}
</style>
