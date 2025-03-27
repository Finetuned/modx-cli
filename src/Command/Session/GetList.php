<?php

namespace MODX\CLI\Command\Session;

use MODX\CLI\Command\ListProcessor;

/**
 * A command to get a list of sessions in MODX
 */
class GetList extends ListProcessor
{
    protected $processor = 'security/session/getlist';
    protected $headers = array(
        'id', 'username', 'ip', 'access', 'last_hit'
    );

    protected $name = 'session:list';
    protected $description = 'Get a list of sessions in MODX';

    protected function parseValue($value, $column)
    {
        if ($column === 'access' || $column === 'last_hit') {
            if (!empty($value)) {
                return date('Y-m-d H:i:s', $value);
            }
        }

        return parent::parseValue($value, $column);
    }
}
