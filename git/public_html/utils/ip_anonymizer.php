<?php
class IpAnonymizer {
    private static $ipMap = [];
    private static $mapFile = 'brain/ip_map.json';

    public static function initialize() {
        if (file_exists(self::$mapFile)) {
            self::$ipMap = json_decode(file_get_contents(self::$mapFile), true) ?? [];
        }
    }

    public static function anonymizeIp($ip) {
        if (!isset(self::$ipMap[$ip])) {
            $nextId = count(self::$ipMap);
            self::$ipMap[$ip] = self::generateAlias($nextId);
            file_put_contents(self::$mapFile, json_encode(self::$ipMap, JSON_PRETTY_PRINT));
        }
        return self::$ipMap[$ip];
    }

    private static function generateAlias($n) {
        $result = '';
        while ($n >= 0) {
            $result = chr(($n % 26) + 65) . $result;
            $n = floor($n / 26) - 1;
            if ($n < 0) break;
        }
        return $result;
    }

    public static function getOriginalIp($alias) {
        return array_search($alias, self::$ipMap) ?: $alias;
    }
}
?>