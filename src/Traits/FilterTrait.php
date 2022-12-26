<?php

namespace Aslnbxrz\CrudGenerator\Traits;

use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

trait FilterTrait
{
    public function queryFilter(Request $request)
    {
        $query = QueryBuilder::for($this->modelClass);
        $query->allowedFilters($this->filter($request));
        $this->includes($request, $query);
        $this->appends($request, $query);
        $this->sort($request, $query);
        return $query;
    }

    public function filter(Request $request): array
    {
        $filters = [];
        if (!empty($request->get('filter'))) {
            foreach ($request->get('filter') as $k => $item) {
                $filters[] = AllowedFilter::exact($k);
            }
        }
        return $filters;
    }

    public function includes(Request $request, QueryBuilder $query): QueryBuilder
    {
        return $query->allowedIncludes(!empty($request->get("include")) ? explode(',', $request->get('include')) : []);
    }

    public function appends(Request $request, QueryBuilder $query): QueryBuilder
    {
        return $query->allowedAppends(!empty($request->get("append")) ? explode(',', $request->get('append')) : []);
    }

    public function sort(Request $request, QueryBuilder $query): QueryBuilder
    {
        return $query->allowedSorts($request->get('sort'));
    }
}
