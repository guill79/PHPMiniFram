<?php

namespace Fram\FormBuilder;

class Textarea extends Field
{
    /**
     * @var bool
     */
    protected $spellcheck;

    /**
     * @var string
     */
    protected $content;

    /**
     * @var string
     */
    protected $placeholder;

    public function __construct(array $config = [])
    {
        $this->spellcheck($config['spellcheck'] ?? false);
    }

    public function spellcheck(bool $spellcheck): self
    {
        $this->spellcheck = $spellcheck;

        return $this;
    }

    public function content(?string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function placeholder(string $placeholder): self
    {
        $this->placeholder = $placeholder;

        return $this;
    }

    public function __toString()
    {
        $str = '<textarea ';

        $str .= $this->addAttribute('class');
        $str .= $this->addAttribute('id');
        $str .= $this->addAttribute('name');
        $str .= $this->addAttribute('placeholder');
        // $str .= $this->addAttribute('value');
        $str .= $this->addCustomAttributes();

        $str .= 'spellcheck="' . ($this->spellcheck ? 'true' : 'false') . '" ';
        if ($this->required) $str .= 'required ';

        return $str . '>' . htmlspecialchars($this->content ?? '') . '</textarea>';
    }
}
