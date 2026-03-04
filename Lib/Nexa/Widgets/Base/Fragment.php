<?php
namespace Nexa\Widgets\Base;

class Fragment
{
    private $renderer;

    public function __construct(callable $renderer)
    {
        $this->renderer = $renderer;
    }

    public function show()
    {
        echo call_user_func($this->renderer);
    }

    public function __toString()
    {
        ob_start();
        $this->show();
        return ob_get_clean();
    }
}
