import {usePage} from "@inertiajs/vue3";

const getLocal = () => {
    return usePage().props.lang?.current??'ar';
}
export {
    getLocal,
}
