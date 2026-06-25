import {useDateFormat} from "@vueuse/core";

export function formatDate(date) {
    if(!date) return '- - - - -';
    return useDateFormat(date, 'YYYY-MM-DD');
}
export function formatDateTime(date) {
    if(!date) return '- - - - -';
    return useDateFormat(date, 'YYYY-MM-DD h:mm a');
}
