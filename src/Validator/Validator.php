<?php

namespace Fram\Validator;

/**
 * This class allows to check if a form is correctly filled.
 * @deprecated
 */
class Validator
{
    /**
     * @var array
     */
    private $params;

    /**
     * @var Violation[]
     */
    private $violations = [];

    /**
     * Constructor.
     *
     * @param array $params The parameters fetched from the form.
     */
    public function __construct(array $params)
    {
        $this->params = $params;
    }

    /**
     * Return the violations.
     *
     * @return Violation[]
     */
    public function getViolations(): array
    {
        return $this->violations;
    }

    /**
     * Marks the given keys as required.
     *
     * If the corresponding value of the keys were not given to the constructor,
     * a violation will be emitted.
     *
     * @param string ...$keys The keys we want to be required.
     * @return Validator
     */
    public function require(string ...$keys): self
    {
        foreach ($keys as $key) {
            $value = $this->getValue($key);
            if ($value === null) {
                $this->addViolation($key, 'required');
            }
        }
        return $this;
    }

    /**
     * Ensures that the value corresponding to the given keys is not empty.
     *
     * @param string ...$keys The keys.
     * @return Validator
     */
    public function notEmpty(string ...$keys): self
    {
        foreach ($keys as $key) {
            $value = $this->getValue($key);
            if ($value === null || empty($value)) {
                $this->addViolation($key, 'empty');
            }
        }
        return $this;
    }

    /**
     * Checks whether the form is valid.
     *
     * @return bool true if valid, false else.
     */
    public function isValid(): bool
    {
        return empty($this->violations);
    }

    /**
     * Return the value corresponding to the key or null if it does not exist.
     *
     * @param string $key The key.
     * @return mixed|null
     */
    private function getValue(string $key)
    {
        if (array_key_exists($key, $this->params)) {
            return $this->params[$key];
        }
        return null;
    }

    /**
     * Adds a violation.
     *
     * @param string $key The key of the field violated.
     * @param string $rule The rule violated.
     */
    private function addViolation(string $key, string $rule): void
    {
        $this->violations[$key] = new Violation($key, $rule);
    }

    // public function exists(string $key, Manager $manager, \PDO $pdo)
    // {
    //     if (!$table->exists(['id' => $id])) {
    //         $this->addViolation($key, 'exist')
    //     }

    //     return $this;
    // }
}
