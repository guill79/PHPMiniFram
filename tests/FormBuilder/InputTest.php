<?php

namespace FormBuilder;

use Fram\FormBuilder\Input;
use PHPUnit\Framework\TestCase;

class InputTest extends TestCase
{
    public function testTextSimple()
    {
        $input = new Input('text');
        $this->assertEquals('<input type="text" autocomplete="off" spellcheck="false" />', (string) $input);
    }

    public function testTextClassId()
    {
        $input = (new Input('text'))
            ->id('toto')
            ->class('tata');

        $this->assertEquals('<input type="text" class="tata" id="toto" autocomplete="off" spellcheck="false" />', (string) $input);
    }

    public function testTextAutocompleteSpellcheckRequired()
    {
        $input = (new Input('text'))
            ->autocomplete(true)
            ->spellcheck(true)
            ->required();

        $this->assertEquals('<input type="text" autocomplete="on" spellcheck="true" required />', (string) $input);
    }

    public function testConfig()
    {
        $input = (new Input('text', [
            'autocomplete' => true,
            'spellcheck' => false,
            'required' => true
        ]))->value('salut');

        $this->assertEquals('<input type="text" value="salut" autocomplete="on" spellcheck="false" required />', (string) $input);
    }

    public function testSubmit()
    {
        $input = (new Input('submit'))->value('salut');

        $this->assertEquals('<input type="submit" value="salut" />', (string) $input);
    }
}
