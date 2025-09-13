import './bootstrap';
import '../css/app.css';
import '../css/theme.css';
import { createApp, h } from 'vue';
import { createInertiaApp, Link } from '@inertiajs/vue3';
import PrimeVue from 'primevue/config';
import 'primeicons/primeicons.css';
import { module_exist, module_not_exist } from "@/directive/ModuleDirective.js";
import Tooltip from 'primevue/tooltip';
import ElPanel from "@/Components/Main/ElPanel.vue";
import ElText from "@/Components/Text/ElText.vue";
import ToastService from 'primevue/toastservice';
import ConfirmationService from "primevue/confirmationservice";
import { createI18n } from 'vue-i18n';
import CKEditor from '@mayasabha/ckeditor4-vue3';
import LoginLayout from "@/Layout/LoginLayout.vue";
import MasterLayout from "@/Layout/MasterLayout.vue";
import ClientLayout from "@/Layout/Clients/ClientLayout.vue";
import { ZiggyVue } from '../../vendor/tightenco/ziggy/dist/index.esm.js';
import { MyThemePreset } from "@/theme.js";

const LANGUAGE_IMPORTS = {
    en: import.meta.glob('./Lang/en/*.js', { eager: true, import: "default" }),
    ar: import.meta.glob('./Lang/ar/*.js', { eager: true, import: "default" }),
};
const locales = Object.keys(LANGUAGE_IMPORTS);
import ganttastic from '@infectoone/vue-ganttastic'
import ElContainer from "@/Components/Card/ElContainer.vue";

const generateLocaleMessages = () =>
    locales.reduce(
        (messages, locale) => ({
            ...messages,
            [locale]: Object.values(LANGUAGE_IMPORTS[locale]).reduce(
                (message, current) => ({ ...message, ...current }),
                {}
            ),
        }),
        {}
    );

const defaultFirstDayOfWeek = 6; // Default weekday start
const primeVueDarkModeSelector = '.dark-mode';

const getPrimeVueLocaleOptions = (isArabic) =>
    isArabic
        ? {
            am: "ص",
            pm: "م",
            dayNames: ["الأحد", "الاثنين", "الثلاثاء", "الأربعاء", "الخميس", "الجمعة", "السبت"],
            dayNamesShort: ["الأحد", "الاثنين", "الثلاثاء", "الأربعاء", "الخميس", "الجمعة", "السبت"],
            dayNamesMin: ["الأحد", "الاثنين", "الثلاثاء", "الأربعاء", "الخميس", "الجمعة", "السبت"],
            monthNames: ["يناير", "فبراير", "مارس", "أبريل", "ماي", "يونيو", "يوليو", "أغسطس", "سبتمبر", "أكتوبر", "نوفمبر", "ديسمبر"],
            monthNamesShort: ["يناير", "فبراير", "مارس", "أبريل", "ماي", "يونيو", "يوليو", "أغسطس", "سبتمبر", "أكتوبر", "نوفمبر", "ديسمبر"],
            chooseYear: "اختر سنة",
            chooseMonth: "اختر شهر",
            chooseDate: "اختر تاريخ",
            prevYear: "السنة السابقة",
            nextYear: "السنة التالية",
            prevMonth: "الشهر السابق",
            nextMonth: "الشهر التالي",
            today: "اليوم",
            firstDayOfWeek: defaultFirstDayOfWeek,
            clear: "مسح",
        }
        : {
            firstDayOfWeek: defaultFirstDayOfWeek,
            am: "AM",
            pm: "PM",
        };

const resolvePage = (name) => {
    const pages = import.meta.glob('./Pages/**/*.vue', { eager: true });
    let page = pages[`./Pages/${name}.vue`];
    if (!page.default?.layout) {
        page.default.layout = [
            'Soon',
            'Auth/Login',
            'PurchasesRequests/ApplyOffer',
            'Meeting/SuccessConfirmation',
            'SaleSchedule/Index',
            'Survey/SurveyRecipient',
            'Visitor/ViewSignedArchive',
            'Clients/Auth/Login',
        ].some(prefix => name.startsWith(prefix))
            ? LoginLayout
            : (name.startsWith('Clients') ? ClientLayout : MasterLayout);
    }
    return page;
};

createInertiaApp({
    title: (title) => (title ? `${title} - ${import.meta.env.VITE_APP_NAME || 'Laravel'}` : import.meta.env.VITE_APP_NAME || 'Laravel'),
    resolve: resolvePage,
    setup({ el, App, props, plugin }) {
        const i18n = createI18n({
            locale: props.initialPage.props.lang.current,
            messages: generateLocaleMessages() || [],
            legacy: false,
        });

        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(i18n)
            .use(ZiggyVue)
            .use(PrimeVue, {
                theme: {
                    preset: MyThemePreset,
                    options: { darkModeSelector: primeVueDarkModeSelector },
                },
                locale: getPrimeVueLocaleOptions(props.initialPage.props.lang.current === "ar"),
            })
            .use(ToastService)
            .use(ConfirmationService)
            .use(CKEditor)
            .use(ganttastic)
            .component('Link', Link)
            .component('ElPanel', ElPanel)
            .component('ElText', ElText)
            .component('ElContainer', ElContainer)
            .directive('has-module', module_exist)
            .directive('else-has-module', module_not_exist)
            .directive('tooltip', Tooltip)
            .mixin({
                methods: {
                    numberFormat(value) {
                        return Intl.NumberFormat().format(value);
                    },
                    addDays(date, days) {
                        const result = new Date(date);
                        result.setDate(result.getDate() + days);
                        return result;
                    },
                },
            })
            .mount(el);
    },
}).then(() => {});
