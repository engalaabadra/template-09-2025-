

## Suggested Project File Structure

app/

├── Enums/

│ └── ServiceResponseEnum.php

│ └── (your enums here)

├── Exceptions

│   │   ├── ApiResponseException.php

│   │   └── RegisterExceptionHandlers.php

├── Exports

│   │   └── ExcelExport.php

│   ├── Helpers

│   │   ├── ReportHelper.php

│   │   ├── RoleHelper.php

│   │   ├── auth.php

│   │   ├── general.php

│   │   ├── models.php

│   │   └── payments.php

├── Http

│   │   ├── Controllers

│   │   │   ├── Auth

│   │   │   │   ├── Admin

│   │   │   │   │   └── LoginController.php

│   │   │   │   └── User

│   │   │   │       ├── LoginController.php

│   │   │   │       ├── RecoveryPasswordController.php

│   │   │   │       └── RegisterController.php

│   │   │   ├── Dashboard

│   │   │   │   ├── Auth

│   │   │   │   │   ├── RoleController.php

│   │   │   │   │   └── UserController.php

│   │   │   │   ├── Geocode

│   │   │   │   │   └── CountryController.php

│   │   │   │   ├── BannerController.php

│   │   │   │   ├── BoardController.php

│   │   │   │   ├── ChatController.php

│   │   │   │   ├── (your controllers here)

│   │   │   ├── User

│   │   │   │   ├── Geocode

│   │   │   │   │   └── CountryController.php

│   │   │   │   ├── BannerController.php

│   │   │   │   ├── BoardController.php

│   │   │   │   ├── ChatController.php

│   │   │   │   ├── (your controllers here)

│   │   │   ├── BaseController.php

│   │   │   ├── Controller.php

│   │   │   ├── LanguageController.php

│   │   │   └── NotificationController.php

│   │   ├── Middleware

│   │   │   ├── CheckIfAdmin.php

│   │   │   ├── HandleInertiaRequests.php

│   │   │   └── SetAuthUser.php

│   │   └── Requests

│   │       ├── Auth

│   │       │   ├── Admin

│   │       │   │   └── LoginRequest.php

│   │       │   └── User

│   │       │       ├── CheckCodeRequest.php

│   │       │       ├── ForgotPasswordRequest.php

│   │       │       ├── LoginRequest.php

│   │       │       ├── RegisterRequest.php

│   │       │       └── ResetPasswordRequest.php

│   │       ├── Dashboard

│   │       │   ├── Auth

│   │       │   │   ├── RoleRequest.php

│   │       │   │   └── UserRequest.php

│   │       │   ├── Geocode

│   │       │   │   └── CountryRequest.php

│   │       │   ├── BannerRequest.php

│   │       │   ├── BoardRequest.php

│   │       │   ├── ChatRequest.php

│   │       │   ├── (your requests here)

│   │       ├── File

│   │       │   ├── DeleteFilesRequest.php

│   │       │   ├── UploadFileRequest.php

│   │       │   └── UploadFilesRequest.php

│   │       ├── Image

│   │       │   ├── UploadImageRequest.php

│   │       │   └── UploadImagesRequest.php

│   │       ├── Notification

│   │       │   ├── SendNotificationRequest.php

│   │       │   └── UpdateFcmRequest.php

│   │       ├── User

│   │       │   ├── Profile

│   │       │   │   ├── UpdatePasswordRequest.php

│   │       │   │   └── UpdateProfileRequest.php

│   │       │   ├── ChatRequest.php

│   │       ├── ActivationActionRequest.php

│   │       ├── BaseBulkActionRequest.php

│   │       ├── BaseRequest.php

│   │       ├── BulkActivationActionRequest.php

│   │       ├── BulkDeleteActionRequest.php

│   │       ├── BulkRestoreActionRequest.php

│   │       ├── RestoreActionRequest.php

│   │       └── SavedActionRequest.php

│   ├── Models

│   │   ├── Builders

│   │   │   ├── BannerBuilder.php

│   │   │   ├── BaseBuilder.php

│   │   │   ├── BoardBuilder.php

│   │   │   ├── (your builders here)

│   │   ├── Filters

│   │   │   ├── Order

│   │   │   │   └── OrderStatusFilter.php

│   │   │   ├── (your fiters here)

│   │   │   ├── ActiveFilter.php

│   │   │   ├── CreatedAtDateRangeFilter.php

│   │   │   └── LangFilter.php

│   │   ├── Geocodes

│   │   │   └── Country.php

│   │   ├── Traits

│   │   │   ├── Accessors

│   │   │   │   └── SmartAttributesTrait.php

│   │   │   ├── Relations

│   │   │   │   ├── Media

│   │   │   │   │   ├── HasFileRelationTrait.php

│   │   │   │   │   ├── HasFilesRelationTrait.php

│   │   │   │   │   ├── HasImageRelationTrait.php

│   │   │   │   │   └── HasImagesRelationTrait.php

│   │   │   │   └── TranslationRelationsTrait.php

│   │   │   ├── BaseModelTrait.php

│   │   │   ├── EnumOptionsTrait.php

│   │   │   ├── FillableTrait.php

│   │   │   ├── ForceCascadeDeleteTrait.php

│   │   │   ├── HasGeneralScopes.php

│   │   │   ├── HasMediaTrait.php

│   │   │   ├── HelpersModelTrait.php

│   │   │   ├── MainRolesHandling.php

│   │   │   ├── MainUsersHandling.php

│   │   │   ├── MorphModelTriggerTrait.php

│   │   │   ├── OwnedByUserLocalScopeTrait.php

│   │   │   ├── ReportableTrait.php

│   │   │   └── SafePropsTrait.php

│   │   │   └── (your traits here)

│   │   ├── Banner.php

│   │   ├── BaseModel.php

│   ├── Observers

│   │   ├── FileObserver.php

│   │   ├── RoleObserver.php

│   │   └── UserObserver.php

│   │   └── (your observers here)

│   ├── Providers

│   │   └── AppServiceProvider.php

│   ├── Repositories

│   │   ├── Auth

│   │   │   ├── Admin

│   │   │   │   └── Login

│   │   │   │       ├── LoginRepository.php

│   │   │   │       └── LoginRepositoryInterface.php

│   │   │   └── User

│   │   │       ├── Login

│   │   │       │   ├── LoginRepository.php

│   │   │       │   └── LoginRepositoryInterface.php

│   │   │       ├── Recovery

│   │   │       │   ├── PasswordRepository.php

│   │   │       │   └── PasswordRepositoryInterface.php

│   │   │       └── Register

│   │   │           ├── RegisterRepository.php

│   │   │           └── RegisterRepositoryInterface.php

│   │   ├── Base

│   │   │   ├── BaseRepository.php

│   │   │   └── BaseRepositoryInterface.php

│   │   ├── Dashboard

│   │   │   ├── Auth

│   │   │   │   ├── Role

│   │   │   │   │   ├── RoleRepository.php

│   │   │   │   │   └── RoleRepositoryInterface.php

│   │   │   │   └── User

│   │   │   │       ├── UserRepository.php

│   │   │   │       └── UserRepositoryInterface.php

│   │   │   ├── Banner

│   │   │   │   ├── BannerRepository.php

│   │   │   │   └── BannerRepositoryInterface.php

│   │   ├── Eloquent

│   │   │   ├── EloquentRepository.php

│   │   │   └── EloquentRepositoryInterface.php

│   │   └── User

│   │       ├── Auth

│   │       │   └── User

│   │       │       ├── UserRepository.php

│   │       │       └── UserRepositoryInterface.php
│   │       ├── Banner

│   │       │   ├── BannerRepository.php

│   │       │   └── BannerRepositoryInterface.php

│   │       ├── (your Repositories here)

│   ├── Resources

│   │   ├── Auth

│   │   │   ├── PermissionResource.php

│   │   │   └── RoleResource.php

│   │   ├── Geocode

│   │   │   └── CountryResource.php

│   │   ├── BannerResource.php

│   │   ├── BaseResource.php

│   │   ├── UserBasicResource.php

│   │   └── UserResource.php

│   │   └── (your resources here)

│   ├── Routing

│   │   ├── PendingCustomResourceRegistration.php

│   │   ├── ResourceRegistrarCustom.php

│   │   └── ResourceRegistrarFiles.php

│   ├── Rules

│   │   ├── EmailRule.php

│   │   ├── FileRule.php

│   │   ├── FilesRule.php

│   │   ├── IdsRule.php

│   │   ├── ImageRule.php

│   │   ├── ImagesRule.php

│   │   ├── LargeTextRule.php

│   │   ├── SaudiNationalIdRule.php

│   │   ├── SmallTextRule.php

│   │   ├── UniqueTranslationValue.php

│   │   ├── UniqueWithoutSoftDeletes.php

│   │   └── uniqueActiveAndNotDeleted.php

│   │   └── (your rules here)

│   ├── Scopes

│   │   ├── ActiveScope.php

│   │   ├── GeneralScopes.php

│   │   └── LanguageScope.php

│   │   └── (your scopes here)

│   ├── Services

│   │   ├── Auth

│   │   │   ├── Admin

│   │   │   │   └── Login

│   │   │   │       ├── LoginService.php

│   │   │   │       └── LoginServiceInterface.php

│   │   │   └── User

│   │   │       ├── Login

│   │   │       │   ├── LoginService.php

│   │   │       │   └── LoginServiceInterface.php

│   │   │       ├── Recovery

│   │   │       │   ├── PasswordService.php

│   │   │       │   └── PasswordServiceInterface.php

│   │   │       └── Register

│   │   │           ├── RegisterService.php

│   │   │           └── RegisterServiceInterface.php

│   │   ├── Dashboard

│   │   │   ├── Auth

│   │   │   │   ├── Role

│   │   │   │   │   ├── RoleService.php

│   │   │   │   │   └── RoleServiceInterface.php

│   │   │   │   └── User

│   │   │   │       ├── UserService.php

│   │   │   │       └── UserServiceInterface.php

│   │   │   ├── Banner

│   │   │   │   ├── BannerService.php

│   │   │   │   └── BannerServiceInterface.php

│   │   │   ├── (your services here)

│   │   ├── Eloquent

│   │   │   ├── EloquentService.php

│   │   │   └── EloquentServiceInterface.php

│   │   ├── General

│   │   │   ├── PdfMethods

│   │   │   │   ├── GeneratePdfService.php

│   │   │   │   └── GeneratePdfServiceInterface.php

│   │   │   ├── ProcessCodeMethods

│   │   │   │   ├── ProccessCodesService.php

│   │   │   │   └── ProccessCodesServiceInterface.php

│   │   │   ├── SendingMessageMethods

│   │   │   │   ├── SendingMessagesService.php

│   │   │   │   └── SendingMessagesServiceInterface.php

│   │   │   ├── SendingNotificationMethods

│   │   │   │   ├── SendingNotificationsService.php

│   │   │   │   └── SendingNotificationsServiceInterface.php

│   │   │   └── VonageCheckMethods

│   │   │       ├── VonageCheckValidateNumber.php

│   │   │       └── VonageCheckValidateNumberInterface.php

│   │   ├── Translation

│   │   │   ├── TranslationService.php

│   │   │   └── TranslationServiceInterface.php

│   │   ├── User

│   │   │   ├── Banner

│   │   │   │   ├── BannerService.php

│   │   │   │   └── BannerServiceInterface.php

│   │   │   ├── Payment

│   │   │   │   ├── PaymentGateways

│   │   │   │   │   ├── PaymentGatewayFactory

│   │   │   │   │   │   ├── PaymentGatewayFactory.php

│   │   │   │   │   │   └── PaymentGatewayFactoryInterface.php

│   │   │   │   │   ├── Paypal

│   │   │   │   │   │   ├── Paypal.php

│   │   │   │   │   │   └── PaypalService.php

│   │   │   │   │   ├── Tap

│   │   │   │   │   │   ├── Tap.php

│   │   │   │   │   │   └── TapService.php

│   │   │   │   │   └── Thawani

│   │   │   │   │       ├── Thawani.php

│   │   │   │   │       └── ThawaniService.php

│   │   │   │   ├── PaymentService.php

│   │   │   │   └── WalletService.php

│   │   │   ├── Profile

│   │   │   │   ├── ProfileService.php

│   │   │   │   └── ProfileServiceInterface.php

│   │   └── BaseService.php

│   └── Traits

│       ├── Controllers

│       │   ├── FilterFrontTrait.php

│       │   ├── InertiaShareTrait.php

│       │   ├── SetsBreadcrumbsTrait.php

│       │   ├── UIHelpersTrait.php

│       │   └── WebApiSuccessResponseTrait.php

│       ├── Observers

│       │   └── ProtectedActionsTrait.php

│       ├── Requests

│       ├── Responses

│       │   └── HandlesApiErrors.php

│       └── Services

│           ├── AuthUserTrait.php

│           ├── FilterTrait.php

│           ├── HandlesBulkOperationsTrait.php

│           ├── HandlesServiceTransactions.php

│           ├── OperationsActivationRestoringTrait.php

│           └── SearchTrait.php


