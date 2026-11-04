<?php

namespace App\Support;

use Closure;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Lightweight server-side processor for DataTables AJAX requests.
 *
 * Only whitelisted columns can be searched or ordered, and every request
 * is paginated in the database with LIMIT/OFFSET.
 */
class DataTableResponse
{
    /**
     * @var array<string, string>
     */
    protected array $orderable = [];

    /**
     * @var list<string>
     */
    protected array $searchable = [];

    protected ?Closure $filter = null;

    /**
     * @var array{0: string, 1: string}|null
     */
    protected ?array $defaultOrder = null;

    /**
     * @param  Builder  $query  Base query (already scoped and eager-loaded).
     * @param  Closure(mixed): array<string, mixed>  $transformer
     */
    public function __construct(
        protected Request $request,
        protected Builder $query,
        protected Closure $transformer,
    ) {}

    /**
     * @param  Closure(mixed): array<string, mixed>  $transformer
     */
    public static function make(Request $request, Builder $query, Closure $transformer): static
    {
        return new static($request, $query, $transformer);
    }

    /**
     * Map DataTables column names to database columns that may be ordered.
     *
     * @param  array<string, string>  $columns
     */
    public function orderable(array $columns): static
    {
        $this->orderable = $columns;

        return $this;
    }

    /**
     * Database columns included in the global search.
     *
     * @param  list<string>  $columns
     */
    public function searchable(array $columns): static
    {
        $this->searchable = $columns;

        return $this;
    }

    /**
     * Apply additional request-driven filters (e.g. dropdown filters).
     *
     * @param  Closure(Builder, Request): void  $filter
     */
    public function filter(Closure $filter): static
    {
        $this->filter = $filter;

        return $this;
    }

    public function toResponse(): JsonResponse
    {
        $total = $this->query->toBase()->getCountForPagination();

        if ($this->filter !== null) {
            ($this->filter)($this->query, $this->request);
        }

        $search = trim((string) $this->request->input('search.value', ''));

        if ($search !== '' && $this->searchable !== []) {
            $this->query->where(function (Builder $query) use ($search): void {
                foreach ($this->searchable as $column) {
                    $query->orWhere($column, 'like', '%'.addcslashes($search, '%_\\').'%');
                }
            });
        }

        $filtered = ($search !== '' || $this->filter !== null)
            ? $this->query->toBase()->getCountForPagination()
            : $total;

        $this->applyOrdering();

        $length = min(max((int) $this->request->input('length', 25), 1), 100);
        $start = max((int) $this->request->input('start', 0), 0);

        $rows = $this->query->skip($start)->take($length)->get()->map($this->transformer)->values();

        return response()->json([
            'draw' => (int) $this->request->input('draw', 0),
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $rows,
        ]);
    }

    protected function applyOrdering(): void
    {
        $columnIndex = $this->request->input('order.0.column');
        $columnName = $this->request->input("columns.{$columnIndex}.data");
        $direction = $this->request->input('order.0.dir') === 'asc' ? 'asc' : 'desc';

        if (is_string($columnName) && isset($this->orderable[$columnName])) {
            $this->query->orderBy($this->orderable[$columnName], $direction);

            return;
        }

        if ($this->defaultOrder !== null) {
            $this->query->orderBy($this->defaultOrder[0], $this->defaultOrder[1]);
        }
    }

    /**
     * Ordering used when the request does not specify a valid column.
     */
    public function defaultOrder(string $column, string $direction = 'desc'): static
    {
        $this->defaultOrder = [$column, $direction];

        return $this;
    }
}
