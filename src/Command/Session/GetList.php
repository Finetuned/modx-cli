<?php

namespace MODX\CLI\Command\Session;

use MODX\CLI\Command\ListProcessor;

/**
 * A command to get a list of sessions in MODX
 */
class GetList extends ListProcessor
{
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

    protected function process()
    {
        $criteria = array();
        $options = array();

        $limit = $this->option('limit');
        if ($limit !== null) {
            $options['limit'] = (int) $limit;
        }

        $start = $this->option('start');
        if ($start !== null) {
            $options['offset'] = (int) $start;
        }

        $total = (int) $this->modx->getCount('MODX\\Revolution\\modActiveUser', $criteria);
        $collection = $this->modx->getCollection('MODX\\Revolution\\modActiveUser', $criteria, $options);

        $results = array();
        foreach ($collection as $activeUser) {
            $lastHit = $activeUser->get('lasthit');
            $results[] = array(
                'id' => $activeUser->get('internalKey'),
                'username' => $activeUser->get('username'),
                'ip' => $activeUser->get('ip'),
                'access' => $lastHit,
                'last_hit' => $lastHit,
            );
        }

        return $this->processResponse(array(
            'total' => $total,
            'results' => $results,
            'success' => true,
        ));
    }
}
