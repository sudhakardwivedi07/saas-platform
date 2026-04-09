<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * BaseModel — shared utility methods across all models.
 * Mirrors the reference project's BaseModel pattern.
 */
class BaseModel extends Model
{
    /**
     * Build an HTML <select> options string from a collection.
     */
    public static function options(
        iterable $items,
        mixed $selected = null,
        string $valueColumn = 'id',
        string $labelColumn = 'name'
    ): string {
        $html = '<option value="">--</option>';

        foreach ($items as $item) {
            $value    = $item->{$valueColumn};
            $label    = $item->{$labelColumn};
            $isSelected = $selected && $value == (is_object($selected) ? $selected->id : $selected)
                ? 'selected'
                : '';

            $html .= "<option {$isSelected} value=\"{$value}\">{$label}</option>";
        }

        return $html;
    }

    /**
     * Build a clickable link for DataTables (matches reference UI pattern).
     */
    public static function clickableLink(string $route, string $title, string $subtitle = ''): string
    {
        return '<div class="media align-items-center">
                    <div class="media-body">
                        <h5 class="mb-0 f-13 text-darkest-grey">
                            <a href="' . e($route) . '" class="openRightModal">' . e($title) . '</a>
                        </h5>
                        <p class="mb-0 text-muted f-12">' . e($subtitle) . '</p>
                    </div>
                </div>';
    }
}
