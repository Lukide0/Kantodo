<?php 


namespace Kantodo\Widgets;

use Kantodo\Core\AbstractWidget;

class Input extends AbstractWidget
{
    const AUTOCOMPLETE_OFF = "off";
    const AUTOCOMPLETE_ON = "on";
    const AUTOCOMPLETE_NAME = "name";
    const AUTOCOMPLETE_NAME_PREFIX = "honorific-prefix";
    const AUTOCOMPLETE_FORENAME = "given-name";
    const AUTOCOMPLETE_MIDDLE_NAME = "additional-name";
    const AUTOCOMPLETE_SURNAME = "family-name";
    const AUTOCOMPLETE_NAME_SURFIX = "honorific-suffix";
    const AUTOCOMPLETE_NICK = "nickname";
    const AUTOCOMPLETE_EMAIL = "email";
    const AUTOCOMPLETE_USERNAME = "username";
    const AUTOCOMPLETE_NEW_PASSWORD = "new-password";
    const AUTOCOMPLETE_CURRENT_PASSWORD = "current-password";
    const AUTOCOMPLETE_ONE_TIME_CODE = "one-time-code";


    private $dataAttr = [];

    public function __construct(string $name, string $label, string $type, $value = "", $error = "", array $dataAttributes = [], string $autocomplete = self::AUTOCOMPLETE_OFF) 
    {
        $this->setLabel($label);
        $this->setName($name);
        $this->setType($type);
        $this->setValue($value);
        $this->setError($error);
        $this->setDataAtrributes($dataAttributes);
        $this->setAutocomplete($autocomplete);
    }

    // predefined inputs

    public static function text(string $name, string $label, $value = "", $error = "", array $dataAttributes = [], string $autocomplete = self::AUTOCOMPLETE_OFF)
    {
        $input = new Input($name, $label, "text", $value, $error, $dataAttributes, $autocomplete);
        return $input->getHTML();
    }

    public static function password(string $name, string $label, $value = "", $error = "", array $dataAttributes = [], string $autocomplete = self::AUTOCOMPLETE_OFF)
    {
        $input = new Input($name, $label, "password", $value, $error, $dataAttributes, $autocomplete);
        return $input->getHTML();
    }




    public function setLabel(string $label)
    {
        $this->setOption("label", $label);
        return $this;
    }

    public function setName(string $name)
    {
        $this->setOption("name", $name);
        return $this;
    }

    public function setType(string $type)
    {
        $this->setOption("type", $type);
        return $this;
    }

    public function setError($error)
    {
        $name = $this->getOption("name", false);

        if (is_array($error) && $name !== false  && !empty($error[$name]))
            $this->setOption("error", $error[$name]);
    
        else if (is_string($error) && $error !== "")
            $this->setOption("error", $error);

        return $this;
    }

    public function setDataAtrributes(array $attributes)
    {
        foreach ($attributes as $key => $value) {
            $this->setDataAttribute($key, $value);
        }
        return $this;
    }

    public function setValue($value)
    {
        $this->setOption("value", $value);
        return $this;
    }

    public function setDataAttribute(string $name, string $value)
    {
        $this->dataAttr[$name] = $value;
        return $this;
    }

    public function setAutocomplete(string $mode = self::AUTOCOMPLETE_ON)
    {
        $this->setOption("autocomplete", $mode);
        return $this;
    }

    public function getHTML()
    {
        $disabled = "";
        if ($this->getOption("disabled") === true)
        {
            $disabled = "disabled";
        }

        $error = "";
        if ($this->getOption("error", "") !== "") 
        {
            $error = "error";
        }

        $dataAttributes = implode(" ", 
                            array_map(
                                function($value, $key)
                                {
                                    return "data-{$key}='{$value}'";
                                },
                                $this->dataAttr,
                                array_keys($this->dataAttr)
                            )
                        );
        
        switch ($this->getOption("type")) {
            case 'password':
                return <<<HTML
                    <div class='container'>
                        <label class="text info-focus input-open {$error}">
                            <input type="password" name="{$this->getOption('name')}" value="{$this->getOption('value', '')}" autocomplete="{$this->getOption('autocomplete', 'off')}" {$dataAttributes} required {$disabled}>
                            <span>{$this->getOption("label", "")}</span>
                            <div class="input-close"><span class="material-icons-outlined" data-show="false" onclick="switchPasswordVisibility(event)">visibility</span></div>
                        </label>
                        <div class="error-text">{$this->getOption("error", "")}</div>
                    </div>
                HTML;
                break;
            
            default:
                return <<<HTML
                    <div class='container'>
                        <label class="text info-focus {$error}">
                            <input type="{$this->getOption('type')}" name="{$this->getOption('name')}" value="{$this->getOption('value', '')}" autocomplete="{$this->getOption('autocomplete', 'off')}" required {$disabled}>
                            <span>{$this->getOption("label", "")}</span>
                        </label>
                        <div class="error-text">{$this->getOption("error", "")}</div>
                    </div>
                HTML;
                break;
        }

    }
}



?>