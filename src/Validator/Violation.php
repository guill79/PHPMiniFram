<?php

namespace Fram\Validator;

/**
 * Represents a violation of a form field.
 *
 * This class is used in the Validator.
 * @deprecated
 */
class Violation
{
    /**
     * @var string
     */
    private $key;

    /**
     * @var string
     */
    private $rule;

    private $rules = [
        'required' => 'Le champ %s est requis',
        'empty' => 'Le champ %s ne doit pas être vide',
        'exists' => 'exis'
    ];

    /**
     * Constructor.
     *
     * @param string $key The key of the field violated.
     * @param string $rule The rule violated.
     */
    public function __construct(string $key, string $rule)
    {
        $this->key = $key;
        $this->rule = $rule;
    }

    public function __toString()
    {
        return sprintf($this->rules[$this->rule], $this->key);
    }
}
