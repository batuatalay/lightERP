<?php
class DateHelper {
    private static $date = null;


    /**
     * UUID v4 generate et
     * @return string
     */
    public static function now() {
        self::$date = date('Y-m-d h:i:s');
    }

    public static function get() {
        return self::$date;
    }
}