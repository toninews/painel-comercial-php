<?php
class NavigationService
{
    public static function getDefaultPrivatePage()
    {
        return AppConfig::get('default_private_page', 'DashboardView');
    }

    public static function getAppName()
    {
        return htmlspecialchars(AppConfig::get('name', 'Projeto PHP'), ENT_QUOTES, 'UTF-8');
    }

    public static function getTagline()
    {
        return htmlspecialchars(AppConfig::get('tagline', ''), ENT_QUOTES, 'UTF-8');
    }

    public static function renderMenu($currentClass = '')
    {
        $items = AppConfig::get('menu', []);
        $html = '';

        foreach ($items as $item)
        {
            $class = $item['class'];
            $activeClass = $currentClass === $class ? ' class="active"' : '';
            $label = htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8');
            $icon = htmlspecialchars($item['icon'], ENT_QUOTES, 'UTF-8');
            $href = 'index-login.php?class=' . rawurlencode($class);

            $html .= "<li{$activeClass}><a href=\"{$href}\"><span class=\"nav-icon\"><i class=\"{$icon}\"></i></span><span class=\"nav-label\">{$label}</span></a></li>";
        }

        return $html;
    }

    public static function renderShortcuts()
    {
        $items = AppConfig::get('shortcuts', []);
        $html = '';

        foreach ($items as $item)
        {
            $label = htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8');
            $href = 'index-login.php?class=' . rawurlencode($item['class']);
            $html .= "<li><a href=\"{$href}\">{$label}</a></li>";
        }

        return $html;
    }
}
