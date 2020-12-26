<?php

namespace Modules\Core\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Modules\Core\Repositories\BaseRepository;

/**
 * Class EloquentCoreRepository
 *
 * @package Modules\Core\Repositories\Eloquent
 */
abstract class EloquentBaseRepository implements BaseRepository
{
    /**
     * @var \Illuminate\Database\Eloquent\Model An instance of the Eloquent Model
     */
    protected $model;

    /**
     * @param Model $model
     */
    public function __construct($model)
    {
        $this->model = $model;
    }

    /**
     * @inheritdoc
     */
    public function find($id)
    {
        if (method_exists($this->model, 'translations')) {
            return $this->model->with('translations')->find($id);
        }

        return $this->model->find($id);
    }

    /**
     * @inheritdoc
     */
    public function all()
    {
        if (method_exists($this->model, 'translations')) {
            return $this->model->with('translations')->orderBy('created_at', 'DESC')->get();
        }

        return $this->model->orderBy('created_at', 'DESC')->get();
    }

    /**
     * @inheritdoc
     */
    public function allWithBuilder() : Builder
    {
        if (method_exists($this->model, 'translations')) {
            return $this->model->with('translations');
        }

        return $this->model->query();
    }

    /**
     * @inheritdoc
     */
    public function paginate($perPage = 15)
    {
        if (method_exists($this->model, 'translations')) {
            return $this->model->with('translations')->orderBy('created_at', 'DESC')->paginate($perPage);
        }

        return $this->model->orderBy('created_at', 'DESC')->paginate($perPage);
    }

    /**
     * @inheritdoc
     */
    public function create($data)
    {
        return $this->model->create($data);
    }

    /**
     * @inheritdoc
     */
    public function update($model, $data)
    {
        $model->update($data);

        return $model;
    }

    /**
     * @inheritdoc
     */
    public function destroy($model)
    {
        return $model->delete();
    }

    /**
     * @inheritdoc
     */
    public function allTranslatedIn($lang)
    {
        return $this->model->whereHas('translations', function (Builder $q) use ($lang) {
            $q->where('locale', "$lang");
        })->with('translations')->orderBy('created_at', 'DESC')->get();
    }

    /**
     * @inheritdoc
     */
    public function findBySlug($slug)
    {
        if (method_exists($this->model, 'translations')) {
            return $this->model->whereHas('translations', function (Builder $q) use ($slug) {
                $q->where('slug', $slug);
            })->with('translations')->first();
        }

        return $this->model->where('slug', $slug)->first();
    }

    /**
     * @inheritdoc
     */
    public function findByAttributes(array $attributes)
    {
        $query = $this->buildQueryByAttributes($attributes);

        return $query->first();
    }

    /**
     * @inheritdoc
     */
    public function getByAttributes(array $attributes, $orderBy = null, $sortOrder = 'asc')
    {
        $query = $this->buildQueryByAttributes($attributes, $orderBy, $sortOrder);

        return $query->get();
    }

    /**
     * Build Query to catch resources by an array of attributes and params
     * @param  array $attributes
     * @param  null|string $orderBy
     * @param  string $sortOrder
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function buildQueryByAttributes(array $attributes, $orderBy = null, $sortOrder = 'asc')
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
     * @inheritdoc
     */
    public function findByMany(array $ids)
    {
        $query = $this->model->query();

        if (method_exists($this->model, 'translations')) {
            $query = $query->with('translations');
        }

        return $query->whereIn("id", $ids)->get();
    }

    /**
     * @inheritdoc
     */
    public function clearCache()
    {
        return true;
    }

    public function getItemsBy($params = false)
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


    public function getItem($criteria, $params = false)
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
