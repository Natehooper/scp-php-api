<?php

namespace Scp\Api;
use Scp\Api\Api;
use Scp\Support\Arr;

abstract class ApiModel
{
    /**
     * @var array
     */
    protected $attributes = [];

    /**
     * @var array
     */
    protected $dirty = [];

    /**
     * @var bool
     */
    protected $exists = false;

    /**
     * @var Api
     */
    protected $api;

    public function __construct(array $info = [], Api $api = null)
    {
        $this->api = $api ?: Api::instance();
        $this->attributes = $info + $this->attributes;
    }

    abstract public function path();

    /**
     * @return Api
     */
    public function api()
    {
        return $this->api;
    }

    public function exists()
    {
        return $this->exists;
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * @param bool $exists
     *
     * @return $this
     */
    public function setExists($exists)
    {
        $this->exists = $exists;

        return $this;
    }

    public function save(array $data = [])
    {
        foreach ($data as $key => $value) {
            $this->setAttribute($key, $value);
        }

        if ($this->exists()) {
            return $this->patch();
        }

        return $this->create();
    }

    public function delete(array $data = [])
    {
        $this->api->delete($this->path(), $data);
    }

    /**
     * Return the full model instance, which often has more detail than ones
     * returned from a list search.
     * @return static
     * @throws ApiError
     */
    public function full()
    {
        return (new static(
            (array)$this->api
                ->get($this->path())
                ->data(),
            $this->api
        ))->setExists(true);
    }

    /**
     * @return $this
     */
    public function patch(array $data = [])
    {
        $data += $this->getAndResetDirty();
        $response = $this->api->patch($this->path(), $data);
        $this->attributes = (array) $response->data();

        return $this;
    }

    /**
     * @return $this
     */
    protected function create()
    {
        $response = $this->api->post($this->path(), $this->attributes);
        $this->attributes = (array) $response->data();
        $this->setExists(true);

        return $this;
    }

    public function setAttribute($attribute, $value)
    {
        $this->dirty[$attribute] = $value;
        $this->attributes[$attribute] = $value;
    }

    public function getAttribute($attribute)
    {
        return Arr::get($this->attributes, $attribute);
    }

    public function __set($attribute, $value)
    {
        $this->setAttribute($attribute, $value);
    }

    public function __get($attribute)
    {
        return $this->getAttribute($attribute);
    }

    /**
     * @return array
     */
    protected function getAndResetDirty()
    {
        $dirty = $this->dirty;

        $this->dirty = [];

        return $dirty;
    }

    /**
     * @return ApiQuery
     */
    public static function query()
    {
        return new ApiQuery(new static);
    }
}
