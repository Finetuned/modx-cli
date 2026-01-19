<?php

namespace MODX\CLI\Command\Session;

use MODX\CLI\Command\ListProcessor;

/**
 * A command to get a list of sessions in MODX
 */
class GetList extends ListProcessor
{
    protected $headers = [
        'id', 'access', 'data'
    ];

    protected $name = 'session:list';
    protected $description = 'Get a list of sessions in MODX';

    /**
     * Format raw values for output.
     *
     * @param mixed  $value  The raw column value.
     * @param string $column The column name.
     * @return mixed
     */
    protected function parseValue(mixed $value, string $column)
    {
        if ($column === 'access') {
            if (!empty($value)) {
                if (is_numeric($value)) {
                    return date('Y-m-d H:i:s', (int) $value);
                }

                $timestamp = strtotime((string) $value);
                if ($timestamp !== false) {
                    return date('Y-m-d H:i:s', $timestamp);
                }
            }
        }

        return parent::parseValue($value, $column);
    }

    /**
     * Execute the command.
     *
     * @return integer
     */
    protected function process(): int
    {
        $criteria = [];
        $options = [];

        $limit = $this->option('limit');
        if ($limit !== null) {
            $options['limit'] = (int) $limit;
        }

        $start = $this->option('start');
        if ($start !== null) {
            $options['offset'] = (int) $start;
        }

        $total = (int) $this->modx->getCount('MODX\\Revolution\\modSession', $criteria);
        $collection = $this->modx->getCollection('MODX\\Revolution\\modSession', $criteria, $options);

        $results = [];
        foreach ($collection as $session) {
            $results[] = [
                'id' => $session->get('id'),
                'access' => $session->get('access'),
                'data' => $session->get('data'),
            ];
        }

        return $this->processResponse([
            'total' => $total,
            'results' => $results,
            'success' => true,
        ]);
    }
}
