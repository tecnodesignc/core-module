<?php

namespace Modules\Core\Repositories\Cache;

use Closure;
use Illuminate\Cache\Repository;
use Illuminate\Config\Repository as ConfigRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Core\Repositories\BaseRepository;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use function serialize;

abstract class BaseCacheDecorator implements BaseRepository
{
    /**
     * @var BaseRepository
     */
    protected BaseRepository $repository;

    /**
     * @var Repository
     */
    protected mixed $cache;
    /**
     * @var string The entity name
     */
    protected string $entityName;
    /**
     * @var string The application locale
     */
    protected string $locale;

    /**
     * @var int caching time
     */
    protected mixed $cacheTime;

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct()
    {
        $this->cache = app(Repository::class);
        $this->cacheTime = app(ConfigRepository::class)->get('cache.time', 60);
        $this->locale = app()->getLocale();
    }

    /**
     * @param int $id
     * @return Model|Collection|Builder|array|null
     */
    public function find(int $id): Model|Collection|Builder|array|null
    {
        return $this->remember(function () use ($id) {
            return $this->repository->find($id);
        });
    }

    /**
     * Return a collection of all elements of the resource
     * @return Collection
     */
    public function all(): Collection
    {
        return $this->remember(function () {
            return $this->repository->all();
        });
    }

    /**
     * @return Builder
     */
    public function allWithBuilder() : Builder
    {
        return $this->remember(function () {
            return $this->repository->allWithBuilder();
        });
    }

    /**
     * Paginate the model to $perPage items per page
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->remember(function () use ($perPage) {
            return $this->repository->paginate($perPage);
        });
    }

    /**
     * Create a resource
     * @param  $data
     * @return Model|Collection|Builder|array|null
     */
    public function create($data):Model|Collection|Builder|array|null
    {
        if (method_exists($this->cache->getStore(), 'tags')) {
            $this->cache->tags($this->entityName)->flush();
        }else{
            $this->cache->flush();
        }

        return $this->repository->create($data);
    }

    /**
     * Update a resource
     * @param  $model
     * @param array $data
     * @return Model|Collection|Builder|array|null
     */
    public function update($model, array $data):Model|Collection|Builder|array|null
    {
        if (method_exists($this->cache->getStore(), 'tags')) {
            $this->cache->tags($this->entityName)->flush();
        }else{
            $this->cache->flush();
        }

        return $this->repository->update($model, $data);
    }

    /**
     * Destroy a resource
     * @param  $model
     * @return bool
     */
    public function destroy($model): bool
    {
        if (method_exists($this->cache->getStore(), 'tags')) {
            $this->cache->tags($this->entityName)->flush();
        }else{
            $this->cache->flush();
        }

        return $this->repository->destroy($model);
    }

    /**
     * Return resources translated in the given language
     * @param string $lang
     * @return Collection
     */
    public function allTranslatedIn(string $lang): Collection
    {
        return $this->remember(function () use ($lang) {
            return $this->repository->allTranslatedIn($lang);
        });
    }

    /**
     * Find a resource by the given slug
     * @param string $slug
     * @return Model|Collection|Builder|array|null
     */
    public function findBySlug(string $slug): Model|Collection|Builder|array|null
    {
        return $this->remember(function () use ($slug) {
            return $this->repository->findBySlug($slug);
        });
    }

    /**
     * Find a resource by an array of attributes
     * @param  array $attributes
     * @return Model|Collection|Builder|array|null
     */
    public function findByAttributes(array $attributes): Model|Collection|Builder|array|null
    {
        return $this->remember(function () use ($attributes) {
            return $this->repository->findByAttributes($attributes);
        });
    }

    /**
     * Return a collection of elements who's ids match
     * @param  array $ids
     * @return Collection
     */
    public function findByMany(array $ids): Collection
    {
        return $this->remember(function () use ($ids) {
            return $this->repository->findByMany($ids);
        });
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
        return $this->remember(function () use ($attributes, $orderBy, $sortOrder) {
            return $this->repository->getByAttributes($attributes, $orderBy, $sortOrder);
        });
    }

    /**
     * Clear the cache for this Repositories' Entity
     * @return bool
     */
    public function clearCache(): bool
    {
        $store = $this->cache;

        if (method_exists($this->cache->getStore(), 'tags')) {
            $store = $store->tags($this->entityName);
        }

        return $store->flush();
    }

    /**
     * @param Closure $callback
     * @param string|null $key
     * @return mixed
     */
    protected function remember(Closure $callback, string $key = null): mixed
    {
        $cacheKey = $this->makeCacheKey($key);

        $store = $this->cache;

        if (method_exists($this->cache->getStore(), 'tags')) {
            $store = $store->tags([$this->entityName, 'global']);
        }

        return $store->remember($cacheKey, $this->cacheTime, $callback);
    }

    /**
     * Generate a cache key with the called method name and its arguments
     * If a key is provided, use that instead
     * @param string|null $key
     * @return string
     */
    private function makeCacheKey(string $key = null): string
    {
        if ($key !== null) {
            return $key;
        }

        $cacheKey = $this->getBaseKey();

        $backtrace = debug_backtrace()[2];

        return sprintf("$cacheKey %s %s", $backtrace['function'], serialize($backtrace['args']));
    }

    /**
     * @return string
     */
    protected function getBaseKey(): string
    {
        return sprintf(
            'encorecms -locale:%s -entity:%s',
            $this->locale,
            $this->entityName
        );
    }

    /**
     * Get resources by an array of attributes
     * @param object $params
     * @return LengthAwarePaginator|Collection
     */
    public function getItemsBy(object $params): Collection|LengthAwarePaginator
    {
        return $this->remember(function () use ($params) {
            return $this->repository->getItemsBy($params);
        });
    }


    /**
     * find a resource by id or slug
     * @param string $criteria
     * @param object $params
     * @return Model|Collection|Builder|array|null
     */
    public function getItem(string $criteria, object $params): Model|Collection|Builder|array|null
    {
        return $this->remember(function () use ($criteria, $params) {
            return $this->repository->getItem($criteria, $params);
        });
    }
}
