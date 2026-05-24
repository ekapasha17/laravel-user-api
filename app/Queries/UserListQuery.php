<?php

namespace App\Queries;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class UserListQuery
{
    private const ALLOWED_SORT_COLUMNS = ['name', 'email', 'created_at'];

    private const DEFAULT_SORT_COLUMN = 'created_at';

    private const PER_PAGE = 15;

    /**
     * @param  array{search?: string, page?: int, sortBy?: string}  $params
     */
    public function get(array $params): LengthAwarePaginator
    {
        return User::query()
            ->withCount('orders')
            ->where('active', true)
            ->when($this->hasSearch($params), fn (Builder $query) => $this->applySearch($query, $params['search']))
            ->orderBy($this->resolveSortColumn($params))
            ->paginate(self::PER_PAGE, page: $params['page'] ?? 1);
    }

    /**
     * @param  array{search?: string}  $params
     */
    private function hasSearch(array $params): bool
    {
        return ! empty($params['search']);
    }

    private function applySearch(Builder $query, string $term): Builder
    {
        return $query->where(function (Builder $query) use ($term): void {
            $query->where('name', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%");
        });
    }

    /**
     * @param  array{sortBy?: string}  $params
     */
    private function resolveSortColumn(array $params): string
    {
        $requested = $params['sortBy'] ?? null;

        return in_array($requested, self::ALLOWED_SORT_COLUMNS, strict: true)
            ? $requested
            : self::DEFAULT_SORT_COLUMN;
    }
}
