<?php
namespace App\Traits\Services;

use App\Models\Search;

/**
 * Trait SearchTrait
 *
 * Adds dynamic search capabilities to Eloquent query builders.
 *
 * Features:
 * - Search in direct columns.
 * - Search in translatable fields (via translations relation).
 * - Search across nested relations of arbitrary depth (e.g. user.profile.username).
 * - Quick ID lookup using "#123" format.
 * - Optional logging of search queries and results count.
 *
 * Example:
 * ```php
 * $users = User::query()
 *     ->search(['name', 'email', 'profile.bio'])
 *     ->get();
 * ```
 */
trait SearchTrait{
   
    /**
     * Perform a dynamic search on the model with support for:
     * - Regular columns
     * - Translatable fields (via 'translations' relation)
     * - Nested relations of arbitrary depth (e.g., user.profile.username)
     * - Quick ID search using #123 format
     *
     * @param array $columns      List of columns or relation paths to search in.
     * @param string|null $search_key Optional search term. Defaults to request('search').
     * 
     * @return static Returns the current query builder instance for chaining.
     */
    public function search(array $columns = [], ?string $search_key = null): static
    {
        // Ensure 'created_at' is included in search by default
        if (!in_array('created_at', $columns)) {
            $columns[] = 'created_at';
        }

        // Get the search key from argument or request, trim spaces
        $search_key = $search_key ?? request()->get('search') ?? '';
        if (!$search_key = trim($search_key)) return $this;

        // Return early if no columns provided
        if (empty($columns)) return $this;

        // Quick search by ID using format like #123
        if (str_starts_with($search_key, '#')) {
            return $this->where('id', substr($search_key, 1));
        }

        // Main search closure
        $this->where(function ($query) use ($columns, $search_key) {

            // Determine translatable fields if defined in the model
            $translatable_columns = $this->model::getProp('translationFields') && is_array($this->model::getProp('translationFields'))
                ? $this->model::getProp('translationFields')
                : [];

            foreach ($columns as $column) {

                // Search in nested relations dynamically (any depth)
                if (str_contains($column, '.')) {
                    $this->applyNestedRelationSearch($query, $column, $search_key);
                    continue;
                }

                // Normal search in direct column
                $query->orWhere($column, 'like', "%$search_key%");
            }
        });

        // Optional: log the search key for analytics
        if ($search_key) {
            $this->afterSearchLogging($search_key);
        }

        return $this;
    }

    /**
     * Recursively handle searching inside nested relations of arbitrary depth.
     *
     * Enable dynamic searching through any depth of relations by recursively traversing each part of the relation path until reaching the target column.
     * Example: "user.profile.username" → queries: user → profile → username
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $columnPath Dot-notated relation path (e.g., 'user.profile.username')
     * @param string $searchKey Search term to apply
     */
    protected function applyNestedRelationSearch($query, string $columnPath, string $searchKey)
    {
        //columnPath : 'user.profile.username'
        // Split the column path into parts by '.'
        $parts = explode('.', $columnPath); // $parts = ['user', 'profile', 'username']

        // Take the first part as the current relation
        $relation = array_shift($parts); // $relation = 'user', will parts = ['profile', 'username']

        // Recombine the remaining parts into nextColumn (may be column or further relation)
        $nextColumn = implode('.', $parts); // $nextColumn = 'profile.username'

        // Apply the whereHas on the current relation
        $query->orWhereHas($relation, function ($q) use ($nextColumn, $searchKey) {

            // If there's still a nested relation, recurse
            if (str_contains($nextColumn, '.')) {
                $this->applyNestedRelationSearch($q, $nextColumn, $searchKey);
            } else {
                // Otherwise, apply the search on the column
                $q->where($nextColumn, 'like', "%$searchKey%");
            }
        });
    }

    protected function afterSearchLogging(string $search_key): void
    {
        // try {
            $results_count = $this->count();
            Search::create([
                'user_id'        => auth()->id(),
                'session_id'     => session()->getId(),
                'query'          => $search_key,
                'results_count'  => $results_count,
                'searchable_id'  => null, // ممكن تخليها null إذا مش محدد record واحد
                'searchable_type'=> get_class($this->getModel()), // اسم الموديل: User أو Content
            ]);
        // } catch (\Throwable $e) {
        //     \Log::error('Search log failed: '.$e->getMessage());
        // }
    }
}
