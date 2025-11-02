<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * Class TaskFilter
 *
 * Handles filtering logic for task queries based on validated request input.
 *
 * Supported filters:
 * - created_from (Y-m-d)
 * - created_to (Y-m-d)
 * - assigned_to (int)
 * - status (enum string)
 * - timezone (IANA timezone string)
 *
 * All dates from the client are converted from the provided timezone
 * to UTC before applying the query constraints.
 *
 * @package App\Filters
 */
class TaskFilter
{
    /**
     * Create a new TaskFilter instance.
     *
     * @param  array<string, mixed>  $filters  Validated filter inputs
     */
    public function __construct(protected array $filters) {}

    /**
     * Apply all filter constraints to the given query.
     *
     * @param  Builder|Relation  $query  Base query or Eloquent relation
     * @return Builder  Modified query with all applied filtering conditions
     */
    public function apply(Builder|Relation $query): Builder
    {
        if ($query instanceof Relation) {
            $query = $query->getQuery();
        }
        $table = $query->getModel()->getTable();
        $tz = $this->filters['timezone'] ?? 'UTC';
        if (!empty($this->filters['created_from'])) {
            $query->where("{$table}.created_at", '>=',
                startDateToUtc($this->filters['created_from'], $tz)
            );
        }

        if (!empty($this->filters['created_to'])) {
            $query->where("{$table}.created_at", '<=',
                endDateToUtc($this->filters['created_to'], $tz)
            );
        }

        if (!empty($this->filters['assigned_to'])) {
            $query->where("{$table}.assigned_to", $this->filters['assigned_to']);
        }

        if (!empty($this->filters['status'])) {
            //$query->where("{$table}.status", $this->filters['status']);
            $status = strtolower($this->filters['status']);
            $query->whereRaw("LOWER({$table}.status) = ?", [$status]);
        }

        return $query;
    }
}