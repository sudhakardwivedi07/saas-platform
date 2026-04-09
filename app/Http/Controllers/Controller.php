<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * Controller — base for all controllers.
 *
 * Mirrors the reference project's $data array pattern:
 *   $this->pageTitle = '...' sets $data['pageTitle']
 *   which is passed to all views via $this->data
 */
class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public array $data = [];

    public function __set(string $name, mixed $value): void
    {
        $this->data[$name] = $value;
    }

    public function __get(string $name): mixed
    {
        return $this->data[$name] ?? null;
    }

    public function __isset(string $name): bool
    {
        return isset($this->data[$name]);
    }

    /**
     * Render a view for AJAX (modal) requests.
     * Returns JSON with rendered HTML — matches reference project pattern.
     */
    protected function returnAjax(string $view): array
    {
        $html = view($view, $this->data)->render();
        return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle ?? '']);
    }

    public function __construct()
    {
    }
}
