<?php
use Nexa\Session\Session;

class VendaSessionService
{
    private const KEY = 'list';

    public static function all()
    {
        return Session::getValue(self::KEY) ?: [];
    }

    public static function add($item)
    {
        $list = self::all();
        $list[$item->id_produto] = $item;
        Session::setValue(self::KEY, $list);
    }

    public static function remove($productId)
    {
        $list = self::all();
        unset($list[$productId]);
        Session::setValue(self::KEY, $list);
    }

    public static function clear()
    {
        Session::setValue(self::KEY, []);
    }

    public static function count()
    {
        return count(self::all());
    }

    public static function total()
    {
        $total = 0;

        foreach (self::all() as $item)
        {
            $total += $item->preco * $item->quantidade;
        }

        return $total;
    }
}
