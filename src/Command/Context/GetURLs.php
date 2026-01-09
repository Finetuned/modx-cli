<?php

namespace MODX\CLI\Command\Context;

use MODX\CLI\Command\BaseCmd;
use Symfony\Component\Console\Helper\Table;

/**
 * A command to get a list of context URLs in MODX
 */
class GetURLs extends BaseCmd
{
    const MODX = true;

    protected $name = 'context:geturls';
    protected $description = 'Get a list of context URLs in MODX';

    protected function process()
    {
        $contexts = $this->modx->getCollection(\MODX\Revolution\modContext::class);
        $json = (bool) $this->option('json');

        if (empty($contexts)) {
            if ($json) {
                $this->output->writeln(json_encode([
                    'total' => 0,
                    'results' => [],
                ], JSON_PRETTY_PRINT));
            } else {
                $this->info('No contexts found');
            }
            return 0;
        }

        $results = [];

        /** @var \MODX\Revolution\modContext $context */
        foreach ($contexts as $context) {
            $key = $context->get('key');

            // Prepare the context
            $context->prepare();

            // Get the site URL
            $siteUrl = $context->getOption('site_url', '');

            // Get the site start URL
            $siteStartId = $context->getOption('site_start', null);
            $siteStartUrl = '';

            if ($siteStartId) {
                /** @var \MODX\Revolution\modResource $resource */
                $resource = $this->modx->getObject(\MODX\Revolution\modResource::class, $siteStartId);
                if ($resource) {
                    $siteStartUrl = $this->modx->makeUrl($siteStartId, $key, '', 'full');
                }
            }

            $results[] = [
                'key' => $key,
                'site_url' => $siteUrl,
                'site_start_url' => $siteStartUrl,
            ];
        }

        if ($json) {
            $this->output->writeln(json_encode([
                'total' => count($results),
                'results' => $results,
            ], JSON_PRETTY_PRINT));
        } else {
            $table = new Table($this->output);
            $table->setHeaders(array('Context Key', 'Site URL', 'Site Start URL'));

            foreach ($results as $row) {
                $table->addRow(array(
                    $row['key'],
                    $row['site_url'],
                    $row['site_start_url'],
                ));
            }

            $table->render();
        }

        return 0;
    }
}
