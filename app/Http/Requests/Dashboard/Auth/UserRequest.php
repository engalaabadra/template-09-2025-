<?php

namespace App\Http\Requests\Dashboard\Auth;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\Validation\Rules\Enum;
use App\Enums\ProfileGenderEnum;
use App\Rules\ImageRule;
use App\Rules\FilesRule;
use App\Rules\EmailRule;
use App\Rules\PhoneNumberRule;
use App\Rules\SmallTextRule;
use App\Rules\DateFormatCreatedAtRule;
use App\Rules\UniqueActiveAndNotDeleted;

/**
 * Class UserRequest
 *
 * This request class handles validation for creating or updating users
 * from the dashboard, including phone/email uniqueness and translation support.
 */
class UserRequest extends BaseRequest
{
    protected string $modelClass = \App\Models\Profile::class;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Define validation rules for user creation or update.
     *
     * @return array
     */
    public function rules(): array
    {
        // Retrieve the user ID from route (used for update case to ignore current user)
        // $userId = $this->route('user');

        $userId = $this->getRouteModel('user');

        // Define base validation rules
        $rules = [

            // Phone number: optional, must be valid and unique
            'phone_no' => [
                'sometimes',
                new PhoneNumberRule(),
                new UniqueActiveAndNotDeleted('users', 'phone_no', $userId),
            ],

            // Email: optional, must be valid and unique
            'email' => [
                'sometimes',
                new EmailRule(),
                new UniqueActiveAndNotDeleted('users', 'email', $userId),

            ],

            // Country ID: required if phone number is present, must match phone number
            'country_id' => [
                'required_with:phone_no',
                'numeric'
            ],

            // user name: must follow small text rule
            'username' => [
                'required',
                'unique:profiles,username,' . $this->getProfileIdFromRoute(),
                new SmallTextRule(),
            ],

            // Optional fields with max length validation //

            // Full name: optional, must follow small text rule
            'full_name' => [
                'nullable',
                new SmallTextRule(),
            ],

            // Nickname: nullable, must follow small text rule
            'nick_name' => [
                'nullable',
                new SmallTextRule(),
            ],

            // Gender: nullable, must be a valid enum value
            'gender' => [
                'nullable',
                new Enum(ProfileGenderEnum::class),
            ],

            // Birth date: must be valid format and before today
            'birth_date' => [
                new DateFormatCreatedAtRule(),
                'before:today',
            ],

            // Validate assigned roles
            'roles'       => ['required', 'array'],
            'roles.*'     => Rule::exists('roles', 'id')->where('id', '!=', 1), // Exclude super admin role (ID 1)

            // Validate image and file uploads //

            // Image: must be a valid image 
            'image' => [new ImageRule()],

            // files: must be a valid files
            'files' => [new FilesRule()],

        ];

        // If you want to include translation support, uncomment below
        return $this->dynamicTranslationRules(
            array_merge(
                $rules,
                $this->getIsActiveRuleIfDashboard()
            ),
            $this->modelClass::getProp('translationFields'),
            $this->modelClass::getProp('uniqueFields'),
            $this->modelClass::getProp('requiredFields'),
            $this->getProfileIdFromRoute()
        );
    }

    protected function getProfileIdFromRoute(): ?int
{
    $routeItem = $this->route('user');

    if ($routeItem instanceof \App\Models\User) {
        return $routeItem->profile?->id;
    }

    if (is_numeric($routeItem)) {
        return \App\Models\User::find((int) $routeItem)?->profile?->id;
    }

    return null;
}

    /**
     * Define custom error messages (optional).
     *
     * @return array
     */
    public function messages(): array
    {
        return [];
    }
}
