<?php
namespace App\Services;

use App\Traits\ManagesData;
use Illuminate\Database\Eloquent\Model;
use Closure;

/**
 * This is a base class for all services.
 * It uses the ManagesData trait to handle database operations.
 */
abstract class BaseService
{

    use ManagesData;

    protected Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Retrieves all resources with pagination.
     */
    public function getAll(array $with = [], int $perPage = 15)
    {
        return $this->model->with($with)->latest()->paginate($perPage);
    }

    /**
     * Retrieves a single resource by its ID.
     */
    public function getById(int $id, array $with = [])
    {
        return $this->model->with($with)->findOrFail($id);
    }

    /**
     * Creates a new resource in the database.
     */
    public function create(array $data, array $relations = [], ?Closure $additionalLogic = null)
    {
        // calling the trait's storeOrUpdate method
        return $this->storeOrUpdate($data, new $this->model, $relations, $additionalLogic);
    }

    /**
     * Updates an existing resource in the database.
     */
    public function update(int $id, array $data, array $relations = [], ?Closure $additionalLogic = null)
    {
        $record = $this->getById($id);
        // calling the trait's storeOrUpdate method
        return $this->storeOrUpdate($data, $record, $relations, $additionalLogic);
    }

    /**
     * Deletes a resource by its ID.
     */
    public function delete(int $id): bool
    {
        return $this->getById($id)->delete();
    }
}
