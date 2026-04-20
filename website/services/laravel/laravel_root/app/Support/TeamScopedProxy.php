<?php

namespace App\Support;

/**
 * @template-covariant TModel of \Illuminate\Database\Eloquent\Model
 * @mixin TModel
 */
class TeamScopedProxy
{
    /** 
     * @var TModel 
     * @readonly
     */
    protected $model;
    protected int $teamId;

    /**
     * @param TModel $model
     * @param int $teamId
     */
    public function __construct($model, int $teamId)
    {
        $this->model = $model;
        $this->teamId = $teamId;
    }

    /**
     * @param string $method
     * @param array<int, mixed> $arguments
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        return RunInTeamScope::run($this->teamId, function () use ($method, $arguments) {
            $this->unsetRelations();

            try {
                return $this->model->{$method}(...$arguments);
            } finally {
                $this->unsetRelations();
            }
        });
    }

    /**
     * Clear roles and permissions relations to prevent stale caching across team contexts.
     */
    protected function unsetRelations(): void
    {
        foreach (['roles', 'permissions'] as $relation) {
            if (method_exists($this->model, $relation)) {
                $this->model->unsetRelation($relation);
            }
        }
    }
}