<template>
    <div
        class="flex flex-col p-0 m-0 w-full text-lg font-medium leading-5  box-border text-slate-900">
        <!-- User Card -->
        <div
            class="flex items-center py-3 px-4 text-sm font-medium leading-5 box-border text-slate-900 dark:text-primary-100 border-b border-gray-100 dark:border-dark-100"
            style="direction: rtl; list-style: outside none none;">
            <!--begin::Avatar-->
            <div class="inline-block relative shrink-0 ml-3 font-medium box-border"
                 style="direction: rtl; list-style: outside none none;">
                <Avatar :image="$page.props.auth.avatar_url"
                        :label="!$page.props.auth.avatar ? $page.props.auth.user?.name.charAt(0).toUpperCase() : null"
                        class="shadow-sm  shadow-5xl ml-3" size="xlarge"/>
            </div>
            <!--end::Avatar-->

            <!--begin::Username-->
            <div
                class="flex flex-col font-medium box-border text-primary-500"
                style="direction: rtl; list-style: outside none none;">
                <Link :href="route('auth.edit-profile')">
                    <div
                        class="flex items-center text-lg font-semibold leading-6 box-border"
                        style="direction: rtl; list-style: outside none none;">
                        {{ $page.props.auth?.user?.name }}
                    </div>
                    <a class="text-base text-gray-400 dark:text-gray-300 cursor-pointer box-border hover:text-primary-500"
                       style="text-decoration: none; transition: color 0.2s ease 0s, background-color 0.2s ease 0s; direction: rtl; list-style: outside none none;">
                        {{ $page.props.auth?.user?.email }}
                    </a>
                </Link>
            </div>
            <!--end::Username-->
        </div>
        <!-- End User Card -->
        <div
            class="block p-0 text-sm font-medium leading-5 box-border text-slate-900"
            style="list-style: outside none none;">
            <template v-if="companyHasActiveSubscription()">
                <LinkUserMenu v-if="usePage().props?.other_data?.logged_in_via_user"
                              :href="route('auth.back-to-owner-account')" method="post">
                    <span>{{ $t('message.back_to_your_account') }}</span>
                    <span class="px-1">-</span>
                    <span>{{ usePage().props?.other_data?.logged_in_via_user?.name }}</span>
                </LinkUserMenu>

                <aside v-else-if="$page.props.auth.accounts.length">
                    <div class="ps-4 mt-4 font-bold text-gray-600 dark:text-primary-200">
                        {{ $t('message.toggle_accounts') }}
                    </div>

                    <div v-for="account in $page.props.auth.accounts"
                         @click="switchAccount(route('auth.login-user',account.id))"
                         class="flex grow-0 shrink-0 items-center pb-1 ps-8 font-medium text-gray-700 dark:text-primary-100  cursor-pointer box-border basis-full hover:bg-gray-100 dark:hover:bg-dark-100 hover:text-teal-700">
                        <div>
                            <small>{{ account?.company?.name }}</small>
                            <br>
                            {{ account.name }}
                        </div>
                    </div>
                    <hr class="my-2"/>
                </aside>
                <LinkUserMenu :href="route('company_admin.api-keys.index')"
                              v-if="isCompanyAdmin()"
                              :text="$t('message.show_api_keys')"/>
                <LinkUserMenu v-has-module="Enum.ModuleNameEnum.NOTIFICATIONS"
                              v-if="isDepartmentAdmin()"
                              :href="route('dashboard.notification-management.index')"
                              :text="$t('message.notifications_management')"/>
                <LinkUserMenu  v-has-module="Enum.ModuleNameEnum.COMPANY_SETTING"
                              :href="route('dashboard.company-setting.index')" :text="$t('message.company_setting')"/>

                <LinkUserMenu  v-has-module="Enum.ModuleNameEnum.TEMPLATES"
                              :href="route('dashboard.templates.index')" :text="$t('enums.ModuleNameEnum.templates')"/>
                <hr/>
                <LinkUserMenu :href="route('auth.edit-profile')" :text="$t('message.edit_profile')"/>
                <LinkUserMenu :href="route('company_admin.auth-company.edit')"
                              v-if="isCompanyAdmin()"
                              :text="$t('message.edit')+' - '+usePage().props.auth?.company?.name"/>
            </template>
            <LinkUserMenu :href="route('auth.logout')" method="post" :text="$t('message.log_out')"/>
        </div>

    </div>
</template>

<script setup>
import {Link, usePage} from "@inertiajs/vue3";
import Avatar from "primevue/avatar";
import LinkUserMenu from "@/Layout/Partial/LinkUserMenu.vue";
import {Enum} from "@/enum.js";
import {companyHasActiveSubscription, isDepartmentAdmin, isCompanyAdmin} from "@/Helpers/Functions.js";
import Message from "primevue/message";

const switchAccount = (href) => {
    window.location.href = href
}
</script>

<style scoped>

</style>
