<?php

namespace MODX\CLI;

/**
 * A hack from Revo 2.0 having issues with modX::runProcessor
 */
class Xdom extends \modX
{
    /**
     * @param array $array
     * @param bool  $count
     *
     * @return bool|string
     */
    public function outputArray($array, $count = false)
    {
        if (!is_array($array)) {
            return false;
        }
        if ($count === false) {
            $count = count($array);
        }

        return '{"total":"' . $count . '","results":' . $this->toJSON($array) . ',"success": true}';
    }
}
