import {usePage} from "@inertiajs/vue3";
import {Enum} from "@/enum.js";

// const asset = (file = null) => {
//     if (!file)
//         return null;
//     return usePage().props.asset_url + (usePage().props.asset_url.endsWith('/') ? '' : '/') +
//         (file.startsWith('/') ? file.substring(1) : file);
// }

const getFlashData = (key = null) => {
    let data = usePage().props.other_data.data;
    return key ? data[key] : data;
}
const removeFlashData = (key = null) => {
    let data = usePage().props.other_data.data;
    key ? data[key] = null : data = null;
}
const exportExcel = (key = 'export_excel') => {
    let url = (window.location.href).split("?");
    window.location.href = (typeof url[1] == 'undefined' ? url[0] + '?' + key + '=true' : (window.location.href) + '&' + key + '=true');
}
const getAlignFrozen = () => {
    return usePage().props.lang.current === 'ar' ? 'right' : 'left';
}

const alertMessage = (message = null, type = 'success', make_translate = false) => {
    usePage().props.toastr = [{type: type, title: '', message: message, make_translate: make_translate}];
}

const alertPleaseWaitLoading = () => {
    alertMessage('message.please_wait_loading', 'info', true);
}
const alertMessageHideElement = (event = null, message = null, type = 'success') => {
    let currentTarget = null;
    if (event) {
        currentTarget = event.currentTarget;
        currentTarget.setAttribute('disabled', 'disabled');

        currentTarget.classList.add('hidden!');
    }
    setTimeout(() => {
        if (currentTarget) {
            currentTarget.classList.remove('hidden!');
            currentTarget.removeAttribute('disabled');
        }
    }, 2000);
    if (message)
        usePage().props.toastr = [{type: type, title: '', message: message}];
}
const copy = async (item, toast, t) => {
    toast.add({
        severity: 'success',
        detail: t('message.copied_successfully'),
        life: 3000, // Duration in milliseconds
    });
    if (navigator.clipboard && window.isSecureContext) {
        return navigator.clipboard.writeText(item);
    } else {
        const textArea = document.createElement('textarea');
        textArea.value = item;
        textArea.style.position = 'fixed';
        textArea.style.left = '-999999px';
        textArea.style.top = '-999999px';
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        try {
            document.execCommand('copy');
        } catch (error) {
            console.error('Copy failed', error);
        } finally {
            textArea.remove();
        }
    }
};
const printContent = (content, orientation = null, appendDomStyle = false, duration = 1000) => {
    setTimeout(function () {
        var prtHtml = content;

        // Extract header and footer from content if they exist
        let header = '';
        let footer = '';
        let mainContent = prtHtml;

        // Try to extract header with template-header class
        const headerMatch = prtHtml.match(/<header[^>]*class="[^"]*header-content[^"]*"[^>]*>([\s\S]*?)<\/header>/i);
        if (headerMatch) {
            header = headerMatch[0];
            mainContent = mainContent.replace(header, '');
        }

        // Try to extract footer with template-footer class
        const footerMatch = prtHtml.match(/<footer[^>]*class="[^"]*footer-content[^"]*"[^>]*>([\s\S]*?)<\/footer>/i);
        if (footerMatch) {
            footer = footerMatch[0];
            mainContent = mainContent.replace(footer, '');
        }

        // If no template-header/template-footer classes found, try generic header/footer tags
        // if (!header) {
        //     const genericHeaderMatch = prtHtml.match(/<header[^>]*>([\s\S]*?)<\/header>/i);
        //     if (genericHeaderMatch) {
        //         header = genericHeaderMatch[0];
        //         mainContent = mainContent.replace(header, '');
        //     }
        // }

        // if (!footer) {
        //     const genericFooterMatch = prtHtml.match(/<footer[^>]*>([\s\S]*?)<\/footer>/i);
        //     if (genericFooterMatch) {
        //         footer = genericFooterMatch[0];
        //         mainContent = mainContent.replace(footer, '');
        //     }
        // }

        // Get all stylesheets HTML
        let stylesHtml = '';
        if (appendDomStyle) {
            for (const node of [...document.querySelectorAll('link[rel="stylesheet"], style')]) {
                stylesHtml += node.outerHTML;
            }
        }


        // Open the print window
        let WinPrint = window.open('', '', 'left=0,top=0,width=800,height=900,toolbar=0,scrollbars=0,status=0');

        // Add print-specific CSS for headers and footers
        let printCSS = `<link rel="stylesheet" href="/css/print.css?v=1.2">`;
        if (orientation)
            printCSS += ` <style>@page { size: ${orientation}; }</style>`;

        WinPrint.document.write(`<!DOCTYPE html>
                <html dir="rtl">
                  <head>
                    ${stylesHtml}
                    ${printCSS}
                  </head>
                  <body>
                    <!-- Header that will repeat on every page -->
                    <div class="header-content">
                        ${header}
                    </div>

                    <!-- Main content -->
                    <div class="page">
                      <div class="page-inner main-content">
                        ${mainContent}
                      </div>
                    </div>

                    <!-- Footer that will repeat on every page -->
                    <div class="footer-content">
                        ${footer}
                    </div>
                  </body>
                </html>`);
        WinPrint.document.close();
        WinPrint.focus();
        setTimeout(function () {
            WinPrint.print();
            WinPrint.close();
        }, duration);
    }, 1000);
}

const printContentFromUrl = (event, i_18_object, url, other_data = []) => {
    alertMessageHideElement(event, i_18_object('message.please_wait_while_printing'), 'info');
    axios.post(url, {'other_data': other_data}).then(response => {
        printContent(response.data?.content ?? response.data, response.data?.orientation ?? null);
    });
}

const tryTranslate = (i_18_object, trans_key) => {
    if (!trans_key)
        return trans_key;
    let trans_keys = ['message.', 'enums.', 'column.'];
    for (let i = 0; i < trans_keys.length; i++) {
        let temp_value = i_18_object(trans_keys[i] + trans_key);
        if (!temp_value.startsWith(trans_keys[i])) {
            return temp_value;
        }
    }
    return trans_key;
}
const companyHasActiveSubscription = () => {
    if (!usePage().props.auth?.company)
        return true;
    return !!usePage().props.auth?.company?.active_subscription;
}
const isCompanyAdmin = () => {
    if (usePage().props.auth?.user?.type !== Enum.UserTypeEnum.NORMAL)
        return false;
    if (!usePage().props.auth?.company)
        return true;
    return usePage().props.auth?.user?.department_id === null;
}


const isDepartmentAdmin = () => {
    if (!usePage().props.auth?.company)
        return true;
    return usePage().props.auth?.user?.department_id !== null && usePage().props.auth?.user?.section_id === null;
}

const formatDateToString = (dateString) => {
    const date = new Date(dateString);

    if (!/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}.\d{6}Z$/.test(dateString)) {
        return dateString;
    }


    // Extract date parts
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0'); // Ensure 2 digits
    const day = String(date.getDate()).padStart(2, '0'); // Ensure 2 digits
    const hours = date.getHours();
    const minutes = String(date.getMinutes()).padStart(2, '0'); // Ensure 2 digits

    // Convert to 12-hour format and determine AM/PM
    const hour12 = hours % 12 || 12; // Convert 0 to 12 for 12-hour format
    const amPm = hours >= 12 ? 'PM' : 'AM';

    // Return formatted date
    return `${year}-${month}-${day} ${hour12}:${minutes} ${amPm}`;
};
const toFixed = (number_to_format) => {
    if (!number_to_format)
        return 0;
    number_to_format = number_to_format * 1;
    return (number_to_format).toFixed(2) * 1;
}
const cloneObject = (el_data) => {
    if (!el_data)
        return el_data;
    return JSON.parse(JSON.stringify(el_data));
}

const updateFormFromRow = (el_form, el_row, skipped_keys = []) => {
    let row = cloneObject(el_row);
    let el_keys = Object.keys(el_form)
    for (let i = 0; i < el_keys.length; i++) {
        if (row && row.hasOwnProperty(el_keys[i]) && !skipped_keys.includes(el_keys[i]))
            el_form[el_keys[i]] = row[el_keys[i]] ?? null;
    }
}

const getRandomId = (append = '', length = 8) => {
    const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    return append + '_' + Array.from({length}, () => characters.charAt(Math.floor(Math.random() * characters.length)))
        .join('');
}

const isLastItemInArray = (el_array, el_item) => {
    return el_array[el_array.length - 1] === el_item;
}
const columnIsDeleted = (row) => {
    return row.deleted_at ? "!text-red-500" : null;
}

const isNullOrEmpty = (value) => {
    return (
        value == null || // Checks `null` and `undefined`
        value === [null] ||
        (Array.isArray(value) && value.length === 0) || // Empty array
        (typeof value === 'string' && value.trim() === '') || // Empty or whitespace string
        (typeof value === 'object' && Object.keys(value).length === 0) // Empty object
    );
}
const isNotNull = (value) => {
    return !isNullOrEmpty(value);
}
export {
    exportExcel, getAlignFrozen, copy,
    printContent, printContentFromUrl,
    alertMessage, alertMessageHideElement, alertPleaseWaitLoading, tryTranslate,
    formatDateToString, getFlashData, removeFlashData,
    updateFormFromRow, toFixed, cloneObject, getRandomId,
    isLastItemInArray, isNullOrEmpty, isNotNull,
    companyHasActiveSubscription, isCompanyAdmin, isDepartmentAdmin,
    columnIsDeleted,
}
