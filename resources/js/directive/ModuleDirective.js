import {usePage} from '@inertiajs/vue3'

export const module_exist = {
    beforeMount: (el, binding, vnode) => {
        if (!hasModule(binding.value)) {
            el.classList.add('hidden');
            setTimeout(function () {
                el.remove();
            }, 1);
        }
    },
};
export const module_not_exist = {
    beforeMount: (el, binding, vnode) => {
        if (hasModule(binding.value)) {
            el.classList.add('hidden');
            setTimeout(function () {
                el.remove();
            }, 1);
        }
    },
};

function hasModule(module_name) {
    let modules = usePage().props.modules;
    if (!modules)
        return false;
    if (module_name == null)
        return true;

    return modules.includes(module_name);

}

export {
    hasModule
}
