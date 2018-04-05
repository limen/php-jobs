<?php
namespace Limen\Jobs;

/**
 * Class Helper
 * @package Limen\Jobs
 */
class Helper
{
    /**
     * compare two datetime
     *
     * @param string $a yyyy-mm-dd hh:ii:ss e.g.
     * @param string $b yyyy-mm-dd hh:ii:ss e.g.
     * @return int|bool return false if $a or $b is not valid
     */
    public static function compareDatetime($a, $b)
    {
        $timea = strtotime($a);
        $timeb = strtotime($b);

        if ($timea === false || $timeb === false) {
            return false;
        }

        if ($timea > $timeb) {
            return 1;
        } elseif ($timea < $timeb) {
            return -1;
        }

        return 0;
    }

    /**
     * @param $a
     * @param $b
     * @return bool
     */
    public static function datetimeLE($a, $b)
    {
        $flag = static::compareDatetime($a, $b);

        return $flag === -1 || $flag === 0;
    }

    /**
     * @param $a
     * @param $b
     * @return bool
     */
    public static function datetimeLT($a, $b)
    {
        return static::compareDatetime($a, $b) === -1;
    }

    /**
     * @return false|string
     */
    public static function nowDatetime()
    {
        return date('Y-m-d H:i:s');
    }
}