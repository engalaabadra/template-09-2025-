<?php
namespace App\Traits\Controllers;

use Illuminate\Support\Str;

trait SetsBreadcrumbsTrait
{
    /**
     * Build and set breadcrumb navigation dynamically for dashboard resources.
     *
     * @param string      $resource   Resource name (e.g., 'contacts', 'contents').
     * @param string|null $action     Current action (e.g., 'index', 'show', 'edit', 'create').
     * @param mixed       $modelOrId  Model instance or ID (for show/edit actions).
     * @param string|null $label      Custom label (optional, fallback if model has no name/title).
     *
     * @return void
     */
    protected function setBreadcrumb(string $resource, string $action = null, $modelOrId = null, ?string $label = null)
    {
        // Start breadcrumb with resource index (e.g., Contacts → /contacts)
        $breadcrumb = [
            [
                'label' => __($resource),
                'url'   => route("dashboard.$resource.index"),
            ],
        ];

        // Handle "show" or "edit" actions (need model or ID)
        if ($action === 'show' || $action === 'edit') {
            // Determine ID (if model is passed → getKey(), otherwise use $modelOrId directly)
            $id = $modelOrId instanceof \Illuminate\Database\Eloquent\Model 
                ? $modelOrId->getKey() 
                : $modelOrId;

            // Determine label (use model name/title, or custom label, or fallback "Item")
            $name = $modelOrId instanceof \Illuminate\Database\Eloquent\Model 
                ? ($modelOrId->name ?? $modelOrId->title ?? $label ?? __('Item')) 
                : ($label ?? __('Item'));

            // Add action breadcrumb (e.g., Contact Name → /contacts/{id}/edit)
            $breadcrumb[] = [
                'label' => $name,
                'url'   => route("dashboard.$resource.$action", $id),
            ];

        // Handle other actions (e.g., create)
        } elseif ($action) {
            $breadcrumb[] = [
                'label' => __("actions.$action"),
                'url'   => route("dashboard.$resource.$action", $modelOrId),
            ];
        }

        // Finally, set the breadcrumb
        $this->breadcrumb($breadcrumb);
    }

        public function breadcrumb(?array $items = null): void
    {
        if (!$items || count($items) == 0) return;

        $items = array_filter($items, fn($z) => data_get($z, 'label'));

        Inertia::share([
            'breadcrumb' => [
                ['label' => __('message.home'), 'url' => route('home')],
                ...$items,
            ],
        ]);

        $this->pageTitle(implode(' - ', array_column(array_slice($items, -2, 2, true), 'label')));
    }

    
    public function pageTitle(string $title): void
    {
        Inertia::share(['pageTitle' => $title]);
    }



}
