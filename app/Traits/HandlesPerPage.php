<?php

namespace App\Traits;

use Illuminate\Http\Request;

/**
 * Trait HandlesPerPage
 *
 * Reusable helper for handling per_page query parameter in API list endpoints.
 * Ensures the per_page value respects default and maximum limits defined in config.
 *
 * Usage:
 * ```php
 * class BuildingTaskController extends Controller
 * {
 *     use HandlesPerPage;
 *
 *     public function index(Building $building, Request $request)
 *     {
 *         $tasks = $building->tasks()->paginate($this->getPerPage($request));
 *         return response()->json($tasks);
 *     }
 * }
 * ```
 */
trait HandlesPerPage
{
    /**
     * Get the `per_page` value from the request, applying default and max limits.
     *
     * @param \Illuminate\Http\Request $request
     * @return int The number of items per page
     */
    protected function getPerPage(Request $request): int
    {
        $defaultPerPage = config('pagination.default_per_page', 10);
        $maxPerPage = config('pagination.max_per_page', 50);

        $perPage = (int) $request->input('per_page', $defaultPerPage);

        return max(1, min($perPage, $maxPerPage));
    }
}