
# IdsRule Validation

This class `IdsRule` is a custom Laravel validation rule that handles different input formats for `ids` in bulk actions.

## Supported Inputs

The rule validates the following cases:

1. **String `"all"`**  
   Example:
   ```json
   {
       "ids": "all"
   }
   ```

2. **Array containing `"all"`**
   Example:

   ```json
   {
       "ids": ["all"]
   }
   ```

3. **Single integer**
   Example:

   ```json
   {
       "ids": 5
   }
   ```

4. **Array of integers**
   Example:

   ```json
   {
       "ids": [1, 2, 3]
   }
   ```

5. **Comma-separated string**
   Example:

   ```json
   {
       "ids": "1,2,3"
   }
   ```

6. **Array containing a single comma-separated string**
   Example:

   ```json
   {
       "ids": ["1,2,3"]
   }
   ```

## Implementation

```php
use App\Rules\IdsRule;

$request->validate([
    'ids' => ['required', new IdsRule()],
]);
```

## How it works

1. **"all" cases:**
   If the value is `"all"` or `["all"]`, validation passes immediately.

2. **Single integer:**
   A single integer is valid.

3. **Comma-separated strings:**
   Strings like `"1,2,3"` are split into arrays and checked.

4. **Arrays of integers:**
   Each element must be numeric and an integer (string numbers like `"2"` are allowed).

5. **Invalid input:**
   Any other format fails validation with the message:

   ```
   The ids field must be 'all' or a list of integer IDs.
   ```

## Notes

* Numeric strings are automatically allowed if they represent integers.
* This rule is ideal for bulk actions such as activate, deactivate, delete, or restore where `ids` can be flexible in format.
