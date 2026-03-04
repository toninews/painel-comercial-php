<?php
namespace Nexa\Core;

use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

/**
 * Carrega a classe da aplicação
 */
class AppLoader
{
    protected $directories;
    protected $classMap = array();
    protected $indexed = false;
    
    /**
     * Adiciona um diretório a ser vasculhado
     */
    public function addDirectory($directory)
    {
        $this->directories[] = $directory;
        $this->indexed = false;
    }
    
    /**
     * Registra o AppLoader
     */
    public function register()
    {
        spl_autoload_register(array($this, 'loadClass'));
    }
    
    /**
     * Carrega uma classe
     */
    public function loadClass($class)
    {
        $this->indexDirectories();

        if (isset($this->classMap[$class]) && file_exists($this->classMap[$class]))
        {
            require_once $this->classMap[$class];
            return true;
        }

        return false;
    }

    protected function indexDirectories()
    {
        if ($this->indexed) {
            return;
        }

        $this->classMap = array();

        foreach ((array) $this->directories as $folder)
        {
            if (!file_exists($folder))
            {
                continue;
            }

            foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($folder)) as $entry)
            {
                if ($entry->isFile() && $entry->getExtension() === 'php')
                {
                    $class = $entry->getBasename('.php');

                    if (!isset($this->classMap[$class]))
                    {
                        $this->classMap[$class] = (string) $entry;
                    }
                }
            }
        }

        $this->indexed = true;
    }
}
