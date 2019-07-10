<?php


namespace MService\Payment\Shared\Utils;

class Converter
{
    public static function arrayToObject(array $arr, $object)
    {
        try {
            $className = get_class($object);
            $methods = get_class_methods($className);

            foreach ($methods as $method) {

                preg_match(' /^(set)(.*?)$/i', $method, $results);

                $pre = $results[1] ?? '';

                $k = $results[2] ?? '';

                $k = strtolower(substr($k, 0, 1)) . substr($k, 1);
                if ($pre == 'set' && !is_null($arr[$k])) {
                    $object->$method($arr[$k]);
                }
            }
            return $object;

        } catch (MoMoException $e) {
            print_r($e->getTrace());
        }
    }

    public static function objectToJsonStrNoNull($object)
    {
        $arr = Converter::objectToArray($object);
        return self::arrayToJsonStrNoNull($arr);
    }

    public static function objectToArray($object)
    {
/*        Object must be serializable
        $array = json_decode(json_encode($object), true);
        return $array;
*/
        $arr = [];
        try {
            $className = get_class($object);
            $methods = get_class_methods($className);

            foreach ($methods as $method) {

                preg_match(' /^(get)(.*?)$/i', $method, $results);

                $pre = $results[1] ?? '';

                $k = $results[2] ?? '';

                $k = strtolower(substr($k, 0, 1)) . substr($k, 1);
                if ($pre == 'get') {
                    $arr[$k] = $object->$method();
                }
            }

        } catch (MoMoException $e) {
            print_r($e->getTrace());
        }
        return $arr;
    }

    public static function arrayToJsonStrNoNull(array $arr)
    {
        return json_encode(array_filter($arr, function ($var) {
            return !is_null($var);
        }));
    }

}
