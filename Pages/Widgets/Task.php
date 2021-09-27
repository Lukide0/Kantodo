<?php 


namespace Kantodo\Widgets;

use Kantodo\Core\Base\AbstractWidget;

class Task extends AbstractWidget
{
    const DESCRIPTION_LENGTH = 100;

    /**
     * @var string
     */
    private $title;
    /**
     * @var string
     */
    private $description;
    /**
     * @var bool
     */
    private $checked;

    /**
     *
     * @param   string  $title        
     * @param   string  $description  
     * @param   bool    $completed             
     * @param   array<string,mixed>   $options     
     *
     */
    private function __construct(string $title, string $description, bool $completed = false, array $options = []) {
        $this->title = $title;

        if (strlen($description) >= self::DESCRIPTION_LENGTH) {
            $this->description = substr($description, 0, self::DESCRIPTION_LENGTH - 3) . '...';
        } else {
            $this->description = $description;
        }

        $this->checked = $completed;


        if (isset($options['avatars']))
            $this->setOption('avatars', $options['avatars']);
        if (isset($options['important']))
            $this->setOption('important', $options['important']);
        if (isset($options['comments']))
            $this->setOption('comments', $options['comments']);
        if (isset($options['tags']))
            $this->setOption('tags', $options['tags']); 
    }

    /**
     * Vytvoří úkol
     *
     * @param   string  $title        nadpis
     * @param   string  $description  popis
     * @param   bool    $completed    dokončeno
     * @param   array<string,mixed>   $options      možnosti
     * 
     * @return string
     *
     */
    public static function Create(string $title, string $description, bool $completed = false, array $options = [])
    {
       $task = new Task($title, $description, $completed, $options);

       return $task->GetHTML();
    }

    /**
     * Vrací html elementu
     *
     * @return  string
     */
    public function GetHTML()
    {
        $important = ($this->getOption('important', false)) ? '<div class="important">Important</div>' : "";
        $avatars = implode("", array_map(function($element)
        {
            return "<div class='avatar'>{$element}</div>";
        }, $this->getOption('avatars', [])));

        $tags = implode("", array_map(function($element)
        {
            return "<div class='tag'>{$element}</div>";
        }, $this->getOption('tags', [])));

        $checked = $this->checked ? 'checked' : '';

        return <<<HTML
        <div class="task">
            <header>
                <div>
                    <label class="checkbox">
                        <input type="checkbox" {$checked}>
                        <div class="background"></div>
                    </label>
                    <h4>{$this->title}</h4>
                </div>
                <div>
                    {$important}
                    <button class="flat no-border icon round">more_vert</button>
                </div>
            </header>
            <main>
                <p>{$this->description}</p>
            </main>
            <footer>
                <div class="avatars">
                    {$avatars}
                </div>
                <div class="row">
                    <div class="tags">
                        {$tags}
                    </div>
                    <div class="row middle"><span class="space-small-right">{$this->getOption('comments', '0')} Comments</span><span class="icon round">chat_bubble</span></div>
                </div>
            </footer>
        </div>
        HTML;
    }
}



?>