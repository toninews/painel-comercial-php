<?php
class TemplateService
{
    public static function render($template, array $replacements = [])
    {
        $output = file_get_contents($template);

        foreach ($replacements as $key => $value)
        {
            $output = str_replace('{' . $key . '}', $value, $output);
        }

        return $output;
    }
}
