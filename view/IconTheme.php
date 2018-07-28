<?php
namespace Mindbit\Mipanel\View;

class IconTheme
{
    const BASE = 'img/famfamfam_silk_icons_v013/';

    public static function boolIcon($bool)
    {
        return self::BASE . ($bool ? 'tick.png' : 'cross.png');
    }
}
