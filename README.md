
# Overview

This project follows best practices by organizing code into Services, Traits, and Repositories to achieve clean, maintainable, and testable code.

***focuses on***

- Extending Laravel's routing to cover bulk operations and custom resource methods.
- Enforcing advanced multilingual and soft delete-aware validations.
- Applying global query scopes for active status and locale filtering.
- Standardizing API/service responses for consistency and ease of error handling.
- Using traits, repositories, filters, and builders for modular, reusable, and maintainable code.

This structure and codebase suit applications needing complex resource handling, multilingual support, and clean separation of concerns.

## Use it

- Register your routes using the custom resource registrars to enable bulk actions and file handling routes.
- Apply the validation rules in your form requests to handle multilingual uniqueness and soft delete-aware uniqueness.
- Attach global scopes like `ActiveScope` and `LanguageScope` to your models to automatically filter queries.
- Return responses from your services using the `ServiceResponse` class for consistent API output.
- Organize additional business logic into traits, repositories, filters, and builders as needed.

## How to use:

  - clone the repo.
  - composer install
  - php artisan generate:key
  - php artisan optimize:clear
  - php artisan passport:install
  - php artisan storage:link
  - php artisan migrate:refresh --seed

    OR
  - php artisan migrate:refresh
  - php artisan db:seed
