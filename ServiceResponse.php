<?php

namespace App\Support\Classes;

use Illuminate\Database\Eloquent\Model;

class ServiceResponse
{
    /**
     * @var bool
     */
    private $success;

    /**
     * @var string|null
     */
    private $message;

    /**
     * @var Model|null
     */
    private $model;

    /**
     * @param bool $success
     * @param string|null $message
     * @param Model|null $model
     */
    public function __construct(bool $success, ?string $message = null, ?Model $model = null)
    {
        $this->success = $success;
        $this->message = $message;
        $this->model = $model;
    }

    /**
     * @return bool
     */
    public function success(): bool
    {
        return $this->success;
    }

    /**
     * @return string|null
     */
    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function getType(): ?string
    {
        return $this->success ? 'success' : 'error';
    }

    /**
     * @return Model|null
     */
    public function getModel(): ?Model
    {
        return $this->model;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'message' => $this->message,
            'model' => $this->model,
        ];
    }
}
