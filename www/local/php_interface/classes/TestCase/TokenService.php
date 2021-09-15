<?php

namespace TestCase;

use TestCase\Internal;

class TokenService
{
    private $errors = [];
    private $repository;

    public function __construct()
    {
        $this->repository = new Internal\eicUserTokenTable;
    }

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @param string $error
     */
    public function setError(string $error): void
    {
        $this->errors[] = $error;
    }

    /**
     * @param array $errors
     */
    public function setErrors(array $errors): void
    {
        $this->errors = $errors;
    }

    /**
     * @return bool
     */
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    /**
     * @param array $context
     * @return bool
     */
    public function add(array $context): bool
    {
        $result = null;

        $data = [
            'TOKEN' => $context['TOKEN'],
        ];

        $result = $this->repository->add($data);

        if (!$result->isSuccess()) {

            $this->setErrors($result->getErrorMessages());

            return false;
        }

        return true;
    }

    /**
     * @param array $context
     * @return bool
     */
    public function update(int $id, array $context): bool
    {
        $result = null;
        $data = [];

        if (empty($context)) {
            throw new \Exception();
        }

        if (!empty($context['USER_ID'])) {
            $data['USER_ID'] = $context['USER_ID'];
        }

        $result = $this->repository->update($id, $data);

        if (!$result->isSuccess()) {

            $this->setErrors($result->getErrorMessages());

            return false;
        }

        return true;
    }

    /**
     * @param $id
     * @return bool
     */
    public function delete($id): bool
    {
        $result = null;

        $result = $this->repository->delete($id);

        if (!$result->isSuccess()) {

            $this->setErrors($result->getErrorMessages());

            return false;
        }

        return true;
    }

    /**
     * @return Internal\eicUserTokenTable
     */
    public function getRepository(): Internal\eicUserTokenTable
    {
        return $this->repository;
    }
}