<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Search and Filter Service
 * 
 * Provides reusable search and filtering functionality for Livewire components
 * and other parts of the application.
 */
class SearchFilterService
{
    /**
     * Apply search filters to a query builder
     *
     * @param Builder $query
     * @param array $filters
     * @return Builder
     */
    public function applyFilters(Builder $query, array $filters): Builder
    {
        foreach ($filters as $filter) {
            if (!isset($filter['type']) || !isset($filter['value']) || empty($filter['value'])) {
                continue;
            }

            switch ($filter['type']) {
                case 'search':
                    $this->applySearchFilter($query, $filter);
                    break;
                case 'exact':
                    $this->applyExactFilter($query, $filter);
                    break;
                case 'range':
                    $this->applyRangeFilter($query, $filter);
                    break;
                case 'date_range':
                    $this->applyDateRangeFilter($query, $filter);
                    break;
                case 'in':
                    $this->applyInFilter($query, $filter);
                    break;
                case 'relationship':
                    $this->applyRelationshipFilter($query, $filter);
                    break;
                case 'custom':
                    $this->applyCustomFilter($query, $filter);
                    break;
            }
        }

        return $query;
    }

    /**
     * Apply search filter (LIKE query across multiple columns)
     *
     * @param Builder $query
     * @param array $filter
     * @return void
     */
    protected function applySearchFilter(Builder $query, array $filter): void
    {
        $searchTerm = $filter['value'];
        $columns = $filter['columns'] ?? [];

        if (empty($columns)) {
            return;
        }

        $query->where(function ($q) use ($searchTerm, $columns) {
            foreach ($columns as $column) {
                $q->orWhere($column, 'like', "%{$searchTerm}%");
            }
        });
    }

    /**
     * Apply exact match filter
     *
     * @param Builder $query
     * @param array $filter
     * @return void
     */
    protected function applyExactFilter(Builder $query, array $filter): void
    {
        $column = $filter['column'];
        $value = $filter['value'];
        $operator = $filter['operator'] ?? '=';

        $query->where($column, $operator, $value);
    }

    /**
     * Apply range filter (between two values)
     *
     * @param Builder $query
     * @param array $filter
     * @return void
     */
    protected function applyRangeFilter(Builder $query, array $filter): void
    {
        $column = $filter['column'];
        $min = $filter['min'] ?? null;
        $max = $filter['max'] ?? null;

        if ($min !== null) {
            $query->where($column, '>=', $min);
        }

        if ($max !== null) {
            $query->where($column, '<=', $max);
        }
    }

    /**
     * Apply date range filter
     *
     * @param Builder $query
     * @param array $filter
     * @return void
     */
    protected function applyDateRangeFilter(Builder $query, array $filter): void
    {
        $column = $filter['column'];
        $startDate = $filter['start_date'] ?? null;
        $endDate = $filter['end_date'] ?? null;

        if ($startDate) {
            $query->whereDate($column, '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate($column, '<=', $endDate);
        }
    }

    /**
     * Apply IN filter (value in array)
     *
     * @param Builder $query
     * @param array $filter
     * @return void
     */
    protected function applyInFilter(Builder $query, array $filter): void
    {
        $column = $filter['column'];
        $values = $filter['values'] ?? [];

        if (!empty($values)) {
            $query->whereIn($column, $values);
        }
    }

    /**
     * Apply relationship filter
     *
     * @param Builder $query
     * @param array $filter
     * @return void
     */
    protected function applyRelationshipFilter(Builder $query, array $filter): void
    {
        $relationship = $filter['relationship'];
        $column = $filter['column'];
        $value = $filter['value'];
        $operator = $filter['operator'] ?? '=';

        $query->whereHas($relationship, function ($q) use ($column, $operator, $value) {
            $q->where($column, $operator, $value);
        });
    }

    /**
     * Apply custom filter using a callback
     *
     * @param Builder $query
     * @param array $filter
     * @return void
     */
    protected function applyCustomFilter(Builder $query, array $filter): void
    {
        $callback = $filter['callback'];

        if (is_callable($callback)) {
            $callback($query, $filter['value'], $filter);
        }
    }

    /**
     * Apply sorting to query
     *
     * @param Builder $query
     * @param string $sortBy
     * @param string $sortDirection
     * @param array $allowedColumns
     * @return Builder
     */
    public function applySorting(Builder $query, string $sortBy, string $sortDirection = 'asc', array $allowedColumns = []): Builder
    {
        // Validate sort direction
        $sortDirection = in_array(strtolower($sortDirection), ['asc', 'desc']) ? strtolower($sortDirection) : 'asc';

        // Validate sort column if allowed columns are specified
        if (!empty($allowedColumns) && !in_array($sortBy, $allowedColumns)) {
            return $query;
        }

        // Handle relationship sorting
        if (str_contains($sortBy, '.')) {
            $parts = explode('.', $sortBy, 2);
            $relationship = $parts[0];
            $column = $parts[1];

            return $query->with($relationship)->orderBy(
                $query->getModel()->{$relationship}()->getRelated()->getTable() . '.' . $column,
                $sortDirection
            );
        }

        return $query->orderBy($sortBy, $sortDirection);
    }

    /**
     * Get filter options for dropdowns
     *
     * @param string $model
     * @param string $column
     * @param string $labelColumn
     * @param array $conditions
     * @return Collection
     */
    public function getFilterOptions(string $model, string $column, string $labelColumn = null, array $conditions = []): Collection
    {
        $labelColumn = $labelColumn ?? $column;
        
        $query = $model::query();

        // Apply conditions
        foreach ($conditions as $condition) {
            $query->where($condition['column'], $condition['operator'] ?? '=', $condition['value']);
        }

        return $query->select($column, $labelColumn)
            ->distinct()
            ->orderBy($labelColumn)
            ->get()
            ->map(function ($item) use ($column, $labelColumn) {
                return [
                    'value' => $item->{$column},
                    'label' => $item->{$labelColumn},
                ];
            });
    }

    /**
     * Build search filters array for common use cases
     *
     * @param array $params
     * @return array
     */
    public function buildFilters(array $params): array
    {
        $filters = [];

        // Search filter
        if (!empty($params['search'])) {
            $filters[] = [
                'type' => 'search',
                'value' => $params['search'],
                'columns' => $params['search_columns'] ?? [],
            ];
        }

        // Category filter
        if (!empty($params['category_id'])) {
            $filters[] = [
                'type' => 'exact',
                'column' => 'category_id',
                'value' => $params['category_id'],
            ];
        }

        // Status filter
        if (!empty($params['status'])) {
            $filters[] = [
                'type' => 'exact',
                'column' => 'status',
                'value' => $params['status'],
            ];
        }

        // Date range filter
        if (!empty($params['date_from']) || !empty($params['date_to'])) {
            $filters[] = [
                'type' => 'date_range',
                'column' => $params['date_column'] ?? 'created_at',
                'start_date' => $params['date_from'] ?? null,
                'end_date' => $params['date_to'] ?? null,
            ];
        }

        // Amount range filter
        if (!empty($params['amount_min']) || !empty($params['amount_max'])) {
            $filters[] = [
                'type' => 'range',
                'column' => $params['amount_column'] ?? 'amount',
                'min' => $params['amount_min'] ?? null,
                'max' => $params['amount_max'] ?? null,
            ];
        }

        // User filter
        if (!empty($params['user_id'])) {
            $filters[] = [
                'type' => 'exact',
                'column' => 'user_id',
                'value' => $params['user_id'],
            ];
        }

        return $filters;
    }

    /**
     * Get pagination info for display
     *
     * @param \Illuminate\Pagination\LengthAwarePaginator $paginator
     * @return array
     */
    public function getPaginationInfo($paginator): array
    {
        return [
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'from' => $paginator->firstItem(),
            'to' => $paginator->lastItem(),
            'has_pages' => $paginator->hasPages(),
            'has_more_pages' => $paginator->hasMorePages(),
        ];
    }

    /**
     * Generate query string for filters
     *
     * @param array $filters
     * @return array
     */
    public function generateQueryString(array $filters): array
    {
        $queryString = [];

        foreach ($filters as $key => $value) {
            if (!empty($value)) {
                $queryString[$key] = ['except' => ''];
            }
        }

        return $queryString;
    }

    /**
     * Validate filter input
     *
     * @param array $filters
     * @param array $rules
     * @return array
     */
    public function validateFilters(array $filters, array $rules): array
    {
        $validated = [];

        foreach ($rules as $key => $rule) {
            $value = $filters[$key] ?? null;

            // Apply validation rules
            if (isset($rule['required']) && $rule['required'] && empty($value)) {
                continue;
            }

            if (isset($rule['type'])) {
                switch ($rule['type']) {
                    case 'string':
                        $value = is_string($value) ? trim($value) : '';
                        break;
                    case 'integer':
                        $value = is_numeric($value) ? (int) $value : null;
                        break;
                    case 'array':
                        $value = is_array($value) ? $value : [];
                        break;
                    case 'date':
                        $value = $value ? date('Y-m-d', strtotime($value)) : null;
                        break;
                }
            }

            if (isset($rule['in']) && !in_array($value, $rule['in'])) {
                continue;
            }

            $validated[$key] = $value;
        }

        return $validated;
    }
}
