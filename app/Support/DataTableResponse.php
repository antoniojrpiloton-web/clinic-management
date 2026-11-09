<?php

namespace App\Support;

use Closure;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DataTableResponse
{
    protected array $orderable = [];

    protected array $searchable = [];

    protected ?Closure $filter = null;

    protected ?array $defaultOrder = null;

    public function __construct(
        protected Request $request,
        protected Builder $query,
        protected Closure $transformer,
    ) {}

    public static function make(Request $request, Builder $query, Closure $transformer): static
    {
        return new static($request, $query, $transformer);
    }

    public function orderable(array $columns): static
    {
        $this->orderable = $columns;

        return $this;
    }

    public function searchable(array $columns): static
    {
        $this->searchable = $columns;

        return $this;
    }

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

    public function defaultOrder(string $column, string $direction = 'desc'): static
    {
        $this->defaultOrder = [$column, $direction];

        return $this;
    }
}
