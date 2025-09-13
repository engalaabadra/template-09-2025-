## Exports
#### App/Exports/ExcelExport
A reusable Laravel export class built with **[Maatwebsite/Excel](https://docs.laravel-excel.com/)** to generate Excel files. It supports:
- **Dynamic column headers** with optional translations.
- **Nested key mapping** (e.g., `user.name`).
- **Closure-based custom values**.
- **Automatic Yes/No translation** for boolean fields.

## Features
- **Simple to use** with `Collection` data.
- Supports **custom headers**.
- **Nested object/array** field access.
- **Callable mappings** for computed columns.
- **Localized** column headers and boolean fields.

## Usage Example
```php
use App\Exports\ExcelExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Collection;

$data = collect([
    [
        'id' => 1,
        'user' => ['name' => 'John Doe'],
        'is_active' => 1,
        'custom_field' => 'hello'
    ],
    [
        'id' => 2,
        'user' => ['name' => 'Jane Smith'],
        'is_active' => 0,
        'custom_field' => 'world'
    ],
]);

$map = [
    'ID' => 'id',                                      // Custom header => field
    'User Name' => 'user.name',                        // Nested key
    'Active Status' => 'is_active',                    // Boolean translation
    'Custom Uppercase' => fn($row) => strtoupper($row['custom_field']), // Closure
];

return Excel::download(new ExcelExport($data, $map), 'users.xlsx');
```

## Expected Output
| ID | User Name  | Active Status | Custom Uppercase |
|----|------------|--------------|------------------|
| 1  | John Doe   | Yes          | HELLO            |
| 2  | Jane Smith | No           | WORLD            |

## How It Works
1. **Pass a Laravel `Collection`** and a mapping array to the constructor.
2. **Mapping array**:
   - If the key is numeric → header is generated from the field name.
   - If the key is a string → it is used as a **custom column header**.
   - Values can be:
     - **String** → field name or nested key (e.g., `user.name`).
     - **Closure** → receives the row and returns a custom value.
3. **Headers** are automatically translated from `resources/lang/{locale}/column.php` if available.
4. **Boolean fields** listed in `BOOLEAN_FIELDS` are automatically converted to Yes/No using translations from `message.yes` / `message.no`.
5. Supports **RTL (Right-to-Left)** alignment for Arabic locale.

## Translation Example
`resources/lang/en/column.php`
```php
return [
    'id' => 'ID',
    'name' => 'Name',
    'is_active' => 'Active Status',
];
```
`resources/lang/en/message.php`
```php
return [
    'yes' => 'Yes',
    'no' => 'No',
];
```

##### Event Hook
The `registerEvents` method is used to:
- Enable **RTL mode** automatically when the app locale is Arabic.
