<?php

namespace MODX\CLI\Command\Context;

use MODX\CLI\Command\ListProcessor;

/**
 * A command to get a list of contexts in MODX
 */
class GetList extends ListProcessor
{
    protected $processor = 'Context\GetList';
    protected $headers = [
        'key', 'name', 'description', 'rank'
    ];

    protected $name = 'context:list';
    protected $description = 'Get a list of contexts in MODX';
}
