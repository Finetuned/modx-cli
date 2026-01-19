<?php

namespace MODX\CLI;

/**
 * A hack from Revo 2.0 having issues with modX::runProcessor
 */
class Xdom extends \modX
{
    // phpcs:disable Squiz.Commenting.FunctionComment.TypeHintMissing, Squiz.Commenting.FunctionComment.ScalarTypeHintMissing
    /**
     * @param array   $array The array.
     * @param boolean $count The count.
     *
     * @return boolean|string
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
    // phpcs:enable Squiz.Commenting.FunctionComment.TypeHintMissing, Squiz.Commenting.FunctionComment.ScalarTypeHintMissing
}
