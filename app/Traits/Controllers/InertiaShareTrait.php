<?php

namespace App\Traits\Controllers;

use Inertia\Inertia;
use App\Traits\Controllers\FilterFrontTrait;
use App\Traits\Controllers\SetsBreadcrumbsTrait;

/**
 * Trait InertiaShareTrait
 *
 * Provides reusable helpers for preparing and sharing data
 * between Laravel backend and Inertia-powered frontend.
 *
 * Includes:
 * - Automatic sharing of filters, breadcrumbs, and UI helpers.
 * - Unified rendering for index/detail Inertia pages.
 * - Default create/update form data for views.
 */
trait InertiaShareTrait
{
    use FilterFrontTrait, SetsBreadcrumbsTrait;

    /**
     * Prepare UI-related data for a model.
     *
     * Handles search input, breadcrumbs, and filters for frontend or API use.
     *
     * @param object $model The model to extract filters and prepare UI helpers for.
     * @return array Filters array prepared for frontend or API responses.
     */
    public function prepareUiData($model, $data)
    {
        // === Filters ===
        $filters = $this->getModelFilters($model); // in FilterFrontTrait, Retrieve filters from the model

        // === Fetch Data ===
        $this->useFilter($filters); // in FilterFrontTrait, Share filters with Inertia or frontend

        // === Front-End UI Helpers ===
        Inertia::share(['allowSearch' => true]);   // Render search input

        if (isWebRequest()) { // Check if request is from web (not API)
            $this->setBreadcrumb(modelName($model), 'index');// Setup breadcrumb navigation
            return $this->renderWebIndexPage(modelNameSingular($model). '/Index', $result, [
                'filters' => $filters,
            ]);

        } else {//API
            return $filters;
        }
    }

    public function getCreateUpdateData(): array
    {
        return [
            'form_data' => [
                'is_active' => \App\Enums\IsActiveEnum::getOptionsData()
            ],
        ];

    }
    /**
     * Render a unified Inertia web page response for index or detail views.
     *
     * This method automatically detects whether the provided `$rows` is a collection (for index view)
     * or a single model instance (for detail view), and structures the response data accordingly.
     *
     * It also automatically includes:
     * - `form_data` from `getCreateUpdateData()` for create/update forms (only for index).
     * - A fallback `pageTitle` if not explicitly provided via `$extra`, using the last two
     *   breadcrumb items shared via Inertia.
     *
     * @param string $viewPath The Inertia view path to render (e.g., 'User/Index', 'Project/Show').
     * @param mixed $rows A collection or paginator for index views, or a single model instance for detail views.
     * @param array $extra Additional data to merge into the response (e.g., breadcrumb, abilities, pageTitle).
     *
     * @return \Inertia\Response
     */

    protected function renderWebIndexPage(string $view, $rows, array $extra = [])
    {
        // Merge create/update form data with any extra data passed in
        $extra = array_merge($this->getCreateUpdateData(), $extra);

        // If no custom page title is provided, generate one from the breadcrumb
        if (!array_key_exists('pageTitle', $extra)) {
            $breadcrumb = Inertia::getShared('breadcrumb') ?? [];
            $title = implode(' - ', array_column(array_slice($breadcrumb, -2), 'label'));
            $extra['pageTitle'] = $title ?: __('message.page_title');
        }

        // Render the Inertia view with rows + extra data
        return Inertia::render($view, [
            'rows' => $rows,
            ...$extra,
        ]);
    }
}
