export function startLoading(el_key = null) {
    let element = document.documentElement;
    element.classList.add('is-loading', 'start-loading' + el_key);
    setTimeout(() => {
        stopLoading();
    }, 9000);
}

export function stopLoading(el_key=null) {
    let element = document.documentElement;
    if (element.classList.contains('start-loading'+el_key))
        element.classList.remove('is-loading');
}
