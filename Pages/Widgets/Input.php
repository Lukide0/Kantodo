<?php

declare(strict_types = 1);

namespace Kantodo\Widgets;

use Kantodo\Core\Base\AbstractWidget;

/**
 * Input widget
 */
class Input extends AbstractWidget
{
    // autocomplete konstanty
    const AUTOCOMPLETE_OFF              = 'off';
    const AUTOCOMPLETE_ON               = 'on';
    const AUTOCOMPLETE_NAME             = 'name';
    const AUTOCOMPLETE_NAME_PREFIX      = 'honorific-prefix';
    const AUTOCOMPLETE_FORENAME         = 'given-name';
    const AUTOCOMPLETE_MIDDLE_NAME      = 'additional-name';
    const AUTOCOMPLETE_SURNAME          = 'family-name';
    const AUTOCOMPLETE_NAME_SURFIX      = 'honorific-suffix';
    const AUTOCOMPLETE_NICK             = 'nickname';
    const AUTOCOMPLETE_EMAIL            = 'email';
    const AUTOCOMPLETE_USERNAME         = 'username';
    const AUTOCOMPLETE_NEW_PASSWORD     = 'new-password';
    const AUTOCOMPLETE_CURRENT_PASSWORD = 'current-password';
    const AUTOCOMPLETE_ONE_TIME_CODE    = 'one-time-code';

    /**
     * Data atributy
     *
     * @var array<string,mixed>
     */
    private $dataAttr = [];

    /**
     * Konstruktor
     *
     * @param   string  $name     
     * @param   string  $label    
     * @param   string  $type    
     * @param   array<string,mixed>   $options
     *
     */
    public function __construct(string $name, string $label, string $type, array $options = [])
    {
        // mandatory
        $this->setLabel($label);
        $this->setName($name);
        $this->setType($type);


        if (!isset($options['outline'])) {
            $this->setOutline(true);
        } else {
            $this->setOutline($options['outline']);
        }

        if (isset($options['value'])) {
            $this->setValue($options['value']);
        }

        if (isset($options['error'])) {
            $this->setError($options['error']);
        }

        if (isset($options['dataAttributes'])) {
            $this->setDataAtrributes($options['dataAttributes']);
        }

        if (isset($options['autocomplete'])) {
            $this->setAutocomplete($options['autocomplete']);
        }

        if (isset($options['color'])) {
            $this->setOption('color', $options['color']);
        }

        if (isset($options['classes'])) {
            $this->setOption('classes', $options['classes']);
        }

    }

    ////////////////////
    // předdefinované //
    ////////////////////

    /**
     * Input typu text
     *
     * @param   string  $name     název
     * @param   string  $label    popisek
     * @param   array<string,mixed>   $options  ['outline' => false, 'value' => '', 'error' => '', 'dataAttributes' => [], 'autocomplete' => 'off', 'color' => 'primary']
     *
     * @return  string            input html
     */
    public static function text(string $name, string $label, array $options = [])
    {
        $input = new Input($name, $label, 'text', $options);
        return $input->getHTML();
    }

    /**
     * Input typu password
     *
     * @param   string  $name     název
     * @param   string  $label    popisek
     * @param   array<string,mixed>   $options  ['outline' => false, 'value' => '', 'error' => '', 'dataAttributes' => [], 'autocomplete' => 'off', 'color' => 'primary']
     *
     * @return  string            input html
     */
    public static function password(string $name, string $label, array $options = [])
    {
        $input = new Input($name, $label, 'password', $options);
        return $input->getHTML();
    }

    /**
     * Změní vzhled na border
     *
     * @param   bool  $outline
     *
     * @return  self
     */
    public function setOutline(bool $outline = true)
    {
        $this->setOption('outline', $outline);
        return $this;
    }

    /**
     * Nastaví label
     *
     * @param   string  $label  text
     *
     * @return  self
     */
    public function setLabel(string $label)
    {
        $this->setOption('label', $label);
        return $this;
    }

    /**
     * Nastaví jméno
     *
     * @param   string  $name  jméno
     *
     * @return  self
     */
    public function setName(string $name)
    {
        $this->setOption('name', $name);
        return $this;
    }

    /**
     * Nastaví typ
     *
     * @param   string  $type  typ
     *
     * @return  self
     */
    public function setType(string $type)
    {
        $this->setOption('type', $type);
        return $this;
    }

    /**
     * Nastaví error
     *
     * @param   array<string,string>|string  $error  string nebo array s klíčem, který je stejný jako jméno
     *
     * @return  self
     */
    public function setError($error)
    {
        $name = $this->getOption('name', false);

        if (is_array($error) && $name !== false && !empty($error[$name])) {
            $this->setOption('error', $error[$name]);
        } else if (is_string($error) && $error !== '') {
            $this->setOption('error', $error);
        }

        return $this;
    }

    /**
     * Nastaví data atributy
     *
     * @param   array<string,mixed>  $attributes  atributy
     *
     * @return  self
     */
    public function setDataAtrributes(array $attributes)
    {
        foreach ($attributes as $key => $value) {
            $this->setDataAttribute($key, $value);
        }
        return $this;
    }

    /**
     * Nastaví hodnotu
     *
     * @param   string|int  $value  hodnota
     *
     * @return  self
     */
    public function setValue($value)
    {
        $this->setOption('value', $value);
        return $this;
    }

    /**
     * Nastaví data attribut
     *
     * @param   string  $name   jméno
     * @param   string  $value  hodnota
     *
     * @return  self
     */
    public function setDataAttribute(string $name, string $value)
    {
        $this->dataAttr[$name] = $value;
        return $this;
    }

    /**
     * Nastaví autocomplete
     *
     * @param   string           $mode  mod
     *
     * @return  self
     */
    public function setAutocomplete(string $mode = self::AUTOCOMPLETE_ON)
    {
        $this->setOption('autocomplete', $mode);
        return $this;
    }

    /**
     * Vrátí html
     *
     * @return  string  html input
     */
    public function getHTML()
    {
        $disabled = '';
        if ($this->getOption('disabled') === true) {
            $disabled = 'disabled';
        }

        $dataAttributes = implode(' ',
            array_map(
                function ($value, $key) {
                    return "data-{$key}='{$value}'";
                },
                $this->dataAttr,
                array_keys($this->dataAttr)
            )
        );
        $outline = $this->getOption('outline') === true ? "outline" : "";
        $color   = $this->getOption("color", "");
        $classes = $this->getOption("classes", "");
        $type = $this->getOption('type', "text");
        $errorClass = $this->getOption('error', false) === true ? 'error' : '';

        return <<<HTML
            <label class="text-field {$outline} {$color} space-big-bottom {$classes} {$errorClass}">
                <div class="field">
                    <span>{$this->getOption('label','')}</span>
                    <input type="{$type}"  name="{$this->getOption('name')}" value="{$this->getOption('value','')}" autocomplete="{$this->getOption('autocomplete','off')}" {$disabled} {$dataAttributes}>
                </div>
                <div class="text">{$this->getOption('error','')}</div>
            </label>
        HTML;
    }
}
