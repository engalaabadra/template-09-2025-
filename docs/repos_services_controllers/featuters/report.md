# ReportableTrait Explanation and Usage

This document explains how the `ReportableTrait` works, including report types, filters, and how the sequence of methods interacts.

---

## Purpose

The `ReportableTrait` allows flexible generation of aggregated reports from Eloquent models or query builder tables. It supports:
- Multiple report types (e.g., `by_active`, `by_date`, `users_count_per_role`).
- Dynamic filters applied from request input.
- Custom joins and conditions for complex reports.

All reports use a **standardized configuration** through `getReportConfig` and `getCommonReports`.

---

## Sequence of Execution

1. **Request Handling:**
   - Filters and report types come from the HTTP request.
   - Example request:
     ```http
     GET /api/users?report_types[]=by_active&report_types[]=users_count_per_role&is_active=1
     ```
   - Here, `report_types[]` defines which reports to generate.
   - Other query parameters (`is_active=1`) are treated as filters.

2. **Handle Report:**
   ```php
   $filters = request()->except(['report', 'page', 'export', 'only_trashed', 'report_types']);
   $types = request()->input('report_types');
   $types = is_array($types) && count($types) > 0 ? $types : ['default'];

   $result = [];
   foreach ($types as $type) {
       $result[$type] = $model::generateReport($model, $filters, $type);
   }

   return ['reports' => $result];
   ```
   - `$filters` collects all valid filter fields from the request.
   - `$types` ensures at least one report type is processed.
   - Loops through each type and calls `generateReport`.

3. **Generate Report:**
   ```php
   public static function generateReport($model, array $filters = [], string $type = '')
   ```
   - Validates if the model provides `getReportConfig`.
   - Retrieves report configuration for the type.
   - Builds a query using:
     - Filters applied only to valid table columns.
     - Joins, where conditions, and custom from table if provided.
     - Grouping and raw SQL selection as per configuration.

4. **Report Configuration:**
   ```php
   protected static function getReportConfig($model, string $type): array
   ```
   - Returns a configuration array for a report type.
   - Supports custom types like `users_count_per_role`.
   - Falls back to common reports or default count if type is unknown.

5. **Common Reports:**
   ```php
   protected static function getCommonReports(string $countAlias = 'records_count'): array
   ```
   - Defines reusable report types such as `by_active` and `by_date`.
   - Provides raw SQL and grouping configuration.

6. **Filters:**
   - Only applied if the column exists in the table.
   - Supports single values or arrays.
   - Ensures safe and dynamic filtering for any report type.

---

## Example Response

When multiple types and filters are used, the response looks like:
```json
{
  "status": true,
  "message": "The operation was completed successfully",
  "data": {
    "reports": {
      "users_count_per_role": [
        {"role_name": "superadmin", "users_count": 1},
        {"role_name": "admin", "users_count": 2},
        {"role_name": "user", "users_count": 3}
      ],
      "by_active": [
        {"is_active": 1, "users_count": 3}
      ],
      "by_date": [
        {"created_date": "2025-08-25", "users_count": 3}
      ]
    }
  },
  "meta": []
}
```

- Each report type generates its own dataset.
- Filters like `is_active` are applied to all report types if relevant.

---

## Summary

- `ReportableTrait` allows generating multiple reports in one request.
- Filters must come from request input and are dynamically validated against table columns.
- `getReportConfig` and `getCommonReports` provide modular and reusable configurations.
- Supports complex queries with joins, where conditions, and grouping.
- Response structure is consistent for all report types.

