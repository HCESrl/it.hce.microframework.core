<?php

namespace it\hce\microframework\core\helpers;


class HeaderHelper
{
    private static $header;

    public static function setJsonHeader()
    {
        self::$header = 'Content-type: application/json';
    }

    public static function setXmlHeader()
    {
        self::$header = 'Content-type: text/xml';
    }
    public static function set404Header()
    {
        http_response_code(404);
    }

    public static function set500Header()
    {
        http_response_code(500);
    }

    public static function printHeader()
    {
        if (isset(self::$header)) {
            header(self::$header);
        }
    }
}

