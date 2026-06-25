<?php
namespace App\Helpers;

class ReportHelper
{
    /**
     * Get common report configurations that can be reused across models.
     *
     * @param string $countAlias The alias used for the count column in the SQL.
     * @return array An array of common group-by report configurations.
     */
    public static function getCommonReports(string $countAlias = 'records_count'): array
    {
        return [

            // Group by the 'is_active' column and count the number of records
            'by_active' => [
                'raw'     => "is_active, COUNT(id) as {$countAlias}", // Raw SQL to select is_active and count records
                'groupBy' => ['is_active'], // Group the result by the 'is_active' field
            ],

            // Group by the creation date of the records
            'by_date' => [
                'raw'     => "DATE(created_at) as created_date, COUNT(id) as {$countAlias}", // Raw SQL to extract date and count records
                'groupBy' => [\DB::raw("DATE(created_at)")], // Group the result by date (not full timestamp)
            ],
            
        ];
    }

}
