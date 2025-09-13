<?php

namespace App\Classes\Filter;

/**
 * Enum FilterTypeEnum
 *
 * Defines the available filter types for building filter components.
 *
 * Example:
 * ```php
 * $filter->filterTypeEnum = FilterTypeEnum::DROPDOWN;
 * echo $filter->filterTypeEnum->value; // "dropdown"
 * ```
 */
enum FilterTypeEnum: string
{
    /** Single-selection dropdown list */
    case DROPDOWN = 'dropdown';

    /** Multiple-selection dropdown list */
    case MULTI_SELECT = 'multi_select';

    /** Date range picker (start date - end date) */
    case DATE_RANGE = 'date_range';

    /** Search input with auto-complete suggestions */
    case SEARCH_AUTO_COMPLETE = 'search_auto_complete';

    /** Tree-structured dropdown list */
    case TREE_DROPDOWN = 'tree_dropdown';
}
