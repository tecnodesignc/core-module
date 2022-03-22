<?php

namespace Modules\Core\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Interface CoreRepository
 * @package Modules\Core\Repositories
 */
interface BaseRepository
{
    /**
     * @param int $id
     * @return Model|Collection|Builder|array|null
     */
    public function find(int $id): Model|Collection|Builder|array|null;

    /**
     * Return a collection of all elements of the resource
     * @return Collection
     */
    public function all(): Collection;

    /**
     * @return Builder
     */
    public function allWithBuilder() : Builder;

    /**
     * Paginate the model to $perPage items per page
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    /**
     * Create a resource
     * @param  $data
     * @return Model|Collection|Builder|array|null
     */
    public function create($data): Model|Collection|Builder|array|null;

    /**
     * Update a resource
     * @param  $model
     * @param array $data
     * @return Model|Collection|Builder|array|null
     */
    public function update($model, array $data): Model|Collection|Builder|array|null;

    /**
     * Destroy a resource
     * @param  $model
     * @return bool
     */
    public function destroy($model): bool;

    /**
     * Return resources translated in the given language
     * @param string $lang
     * @return Collection
     */
    public function allTranslatedIn(string $lang): Collection;

    /**
     * Find a resource by the given slug
     * @param string $slug
     * @return Model|Collection|Builder|array|null
     */
    public function findBySlug(string $slug): Model|Collection|Builder|array|null;

    /**
     * Find a resource by an array of attributes
     * @param  array $attributes
     * @return Model|Collection|Builder|array|null
     */
    public function findByAttributes(array $attributes): Model|Collection|Builder|array|null;

    /**
     * Return a collection of elements who's ids match
     * @param  array $ids
     * @return Collection
     */
    public function findByMany(array $ids): Collection;

    /**
     * Get resources by an array of attributes
     * @param  array $attributes
     * @param string|null $orderBy
     * @param string $sortOrder
     * @return Collection
     */
    public function getByAttributes(array $attributes, string $orderBy = null, string $sortOrder = 'asc'): Collection;

    /**
     * Clear the cache for this Repositories' Entity
     * @return bool
     */
    public function clearCache(): bool;


    /**
     * Get resources by an array of attributes
     * @param object $params
     * @return LengthAwarePaginator|Collection
     */
    public function getItemsBy(object $params): Collection|LengthAwarePaginator;


    /**
     * Find a resource by id or slug
     * @param string $criteria
     * @param object $params
     * @return Model|Collection|Builder|array|null
     */
    public function getItem(string $criteria, object $params): Model|Collection|Builder|array|null;
}
