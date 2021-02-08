<?php

namespace Gurucomkz\EagerLoading;

class Utils
{

    public static function EnsureArray($arr, $kfield = null)
    {
        if (is_array($arr)) {
            return $arr;
        }
        $result = [];
        foreach ($arr as $k => $v) {
            $key = $k;
            if ($kfield !== null) {
                if (is_array($v) && isset($v[$kfield])) {
                    $key = $v[$kfield];
                } elseif (is_object($v) && property_exists($v, $kfield)) {
                    $key = $v->$kfield;
                }
            }
            $result[$key] = $v;
        }
        return $result;
    }

    public static function extractField($arr, $field)
    {
        $result = [];
        foreach ($arr as $record) {
            $key = is_object($record) ? $record->ID : $record['ID'];
            $result[$key] = is_object($record) ? $record->$field : $record[$field];
        }
        return $result;
    }
}
