<?php
/**
 * Template class
 * ------------------------------------ 
 * Build the view
 * 
 */

namespace Core\Template;

class Template{

    private $path;
    private $layout;

    public function defineLayout($layout){
        $this->layout = $layout;
    }

    public function make($path, $data = []){
        ob_start();
        $this->path = '/' . str_replace('.', '/', $path) . '.php';
        $fullPath = ROOT . '/app/views' . $this->path;
        if(!file_exists($fullPath)){
            throw new \Exception("Template $fullPath not found");
        }

        extract($data);
        include($fullPath);
        $content = ob_get_contents();
        ob_end_clean();

        if(!$this->layout){
            return include($fullPath);
        }
        return include(ROOT . '/app/' . $this->layout);
    }
}