<?php

class NixFifty_ThreadLimit_Listen
{
    public static function loadClass($class, array &$extend)
    {
        $extend[] = 'NixFifty_ThreadLimit_' . $class;
    }
}