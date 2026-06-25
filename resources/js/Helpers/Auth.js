import {usePage} from "@inertiajs/vue3";

const authCompany = () => {
    return usePage().props.auth?.company;
}
const authUser = () => {
    return usePage().props.auth?.user;
}
const authIsClient = () => {
    return usePage().props.auth?.type === 'App\\Models\\Client';
}
const authIsUser = () => {
    return usePage().props.auth?.type === 'App\\Models\\User';
}
const authId = () => {
    return authUser()?.id;
}
const authClass = () => {
    return usePage().props?.auth?.type
}

const modelCreatedByAuth = (model) => {
    if (model.created_by_id !== authId())
        return false;
    return authClass() === usePage().props.auth?.type;
}
export {
    authCompany, authUser, authIsClient, authId, authIsUser, authClass, modelCreatedByAuth,
}
