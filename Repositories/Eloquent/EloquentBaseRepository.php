<?php

namespace Modules\Core\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Core\Repositories\BaseRepository;

/**
 * Class EloquentCoreRepository
 *
 * @package Modules\Core\Repositories\Eloquent
 */
abstract class EloquentBaseRepository implements BaseRepository
{
    /**
     * @var Model An instance of the Eloquent Model
     */
    protected Model $model;

    /**
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * @param int $id
     * @return Model|Collection|Builder|array|null
     */
    public function find(int $id): Model|Collection|Builder|array|null
    {
        if (method_exists($this->model, 'translations')) {
            return $this->model->with('translations')->find($id);
        }

        return $this->model->find($id);
    }

    /**
     * Return a collection of all elements of the resource
     * @return Collection
     */
    public function all(): Collection
    {
        if (method_exists($this->model, 'translations')) {
            return $this->model->with('translations')->orderBy('created_at', 'DESC')->get();
        }

        return $this->model->orderBy('created_at', 'DESC')->get();
    }

    /**
     * @return Builder
     */
    public function allWithBuilder(): Builder
    {
        if (method_exists($this->model, 'translations')) {
            return $this->model->with('translations');
        }

        return $this->model->query();
    }

    /**
     * Paginate the model to $perPage items per page
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        if (method_exists($this->model, 'translations')) {
            return $this->model->with('translations')->orderBy('created_at', 'DESC')->paginate($perPage);
        }

        return $this->model->orderBy('created_at', 'DESC')->paginate($perPage);
    }

    /**
     * Create a resource
     * @param  $data
     * @return Model|Collection|Builder|array|null
     */
    public function create($data): Model|Collection|Builder|array|null
    {
        return $this->model->create($data);
    }

    /**
     * Update a resource
     * @param  $model
     * @param array $data
     * @return Model|Collection|Builder|array|null
     */
    public function update($model, array $data): Model|Collection|Builder|array|null
    {
        $model->update($data);

        return $model;
    }

    /**
     * Destroy a resource
     * @param  $model
     * @return bool
     */
    public function destroy($model): bool
    {
        return $model->delete();
    }

    /**
     * Return resources translated in the given language
     * @param string $lang
     * @return Collection
     */
    public function allTranslatedIn(string $lang): Collection
    {
        return $this->model->whereHas('translations', function (Builder $q) use ($lang) {
            $q->where('locale', "$lang");
        })->with('translations')->orderBy('created_at', 'DESC')->get();
    }

    /**
     * Find a resource by the given slug
     * @param string $slug
     * @return Model|Collection|Builder|array|null
     */
    public function findBySlug(string $slug): Model|Collection|Builder|array|null
    {
        if (method_exists($this->model, 'translations')) {
            return $this->model->whereHas('translations', function (Builder $q) use ($slug) {
                $q->where('slug', $slug);
            })->with('translations')->first();
        }

        return $this->model->where('slug', $slug)->first();
    }

    /**
     * Find a resource by an array of attributes
     * @param  array $attributes
     * @return Model|Collection|Builder|array|null
     */
    public function findByAttributes(array $attributes): Model|Collection|Builder|array|null
    {
        $query = $this->buildQueryByAttributes($attributes);

        return $query->first();
    }

    /**
     * Return a collection of elements who's ids match
     * @param  array $ids
     * @return Collection
     */
    public function findByMany(array $ids): Collection
    {
        $query = $this->model->query();

        if (method_exists($this->model, 'translations')) {
            $query = $query->with('translations');
        }

        return $query->whereIn("id", $ids)->get();
    }

    /**
     * Get resources by an array of attributes
     * @param  array $attributes
     * @param string|null $orderBy
     * @param string $sortOrder
     * @return Collection
     */
    public function getByAttributes(array $attributes, string $orderBy = null, string $sortOrder = 'asc'): Collection
    {
        $query = $this->buildQueryByAttributes($attributes, $orderBy, $sortOrder);

        return $query->get();
    }

    /**
     * Build Query to catch resources by an array of attributes and params
     * @param array $attributes
     * @param string|null $orderBy
     * @param string $sortOrder
     * @return Builder
     */
    private function buildQueryByAttributes(array $attributes, string $orderBy = null, string $sortOrder = 'asc'): Builder
    {
        $query = $this->model->query();

        if (method_exists($this->model, 'translations')) {
            $query = $query->with('translations');
        }

        foreach ($attributes as $field => $value) {
            $query = $query->where($field, $value);
        }

        if (null !== $orderBy) {
            $query->orderBy($orderBy, $sortOrder);
        }

        return $query;
    }


    /**
     * Clear the cache for this Repositories' Entity
     * @return bool
     */
    public function clearCache():bool
    {
        return true;
    }

    /**
     * Get resources by an array of attributes
     * @param bool|object $params
     * @return LengthAwarePaginator|Collection
     */
    public function getItemsBy(bool|object $params = false): Collection|LengthAwarePaginator
    {
        /*== initialize query ==*/
        $query = $this->model->query();

        /*== RELATIONSHIPS ==*/
        if (in_array('*', $params->include)) {//If Request all relationships
            $query->with([]);
        } else {//Especific relationships
            $includeDefault = [];//Default relationships
            if (isset($params->include))//merge relations with default relationships
                $includeDefault = array_merge($includeDefault, $params->include);
            $query->with($includeDefault);//Add Relationships to query
        }

        /*== FILTERS ==*/
        if (isset($params->filter)) {
            $filter = $params->filter;//Short filter

            //Filter by date
            if (isset($filter->date)) {
                $date = $filter->date;//Short filter date
                $date->field = $date->field ?? 'created_at';
                if (isset($date->from))//From a date
                    $query->whereDate($date->field, '>=', $date->from);
                if (isset($date->to))//to a date
                    $query->whereDate($date->field, '<=', $date->to);
            }

            //Order by
            if (isset($filter->order)) {
                $orderByField = $filter->order->field ?? 'created_at';//Default field
                $orderWay = $filter->order->way ?? 'desc';//Default way
                $query->orderBy($orderByField, $orderWay);//Add order to query
            }

            //add filter by search
            if (isset($filter->search) && $filter->search) {
                //find search in columns
                $term = $filter->search;
                $query->where(function ($subQuery) use ($term) {
                    $subQuery->whereHas('translations', function ($q) use ($term) {
                        $q->where('title', 'LIKE', "%{$term}%");
                    })->orWhere('id', $term);
                });

            }
        }

        /*== FIELDS ==*/
        if (isset($params->fields) && count($params->fields))
            $query->select($params->fields);

        /*== REQUEST ==*/
        if (isset($params->page) && $params->page) {
            return $query->paginate($params->take);
        } else {
            $params->take ? $query->take($params->take) : false;//Take
            return $query->get();
        }
    }

    /**
     * Get resources by an array of attributes
     * @param string $criteria
     * @param bool|object $params
     * @return Model|Collection|Builder|array|null
     */

    public function getItem(string $criteria, $params = false): Model|Collection|Builder|array|null
    {
        //Initialize query
        $query = $this->model->query();

        /*== RELATIONSHIPS ==*/
        if (in_array('*', $params->include)) {//If Request all relationships
            $query->with([]);
        } else {//Especific relationships
            $includeDefault = [];//Default relationships
            if (isset($params->include))//merge relations with default relationships
                $includeDefault = array_merge($includeDefault, $params->include);
            $query->with($includeDefault);//Add Relationships to query
        }

        /*== FILTER ==*/
        if (isset($params->filter)) {
            $filter = $params->filter;


            if (isset($filter->field))//Filter by specific field
                $field = $filter->field;
        }

        /*== FIELDS ==*/
        if (isset($params->fields) && count($params->fields))
            $query->select($params->fields);

        /*== REQUEST ==*/
        return $query->where($field ?? 'id', $criteria)->first();
    }


}
