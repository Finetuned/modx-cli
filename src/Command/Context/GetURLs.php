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
        $contexts = $this->modx->getCollection('modContext');

        if (empty($contexts)) {
            $this->info('No contexts found');
            return 0;
        }

        $table = new Table($this->output);
        $table->setHeaders(array('Context Key', 'Site URL', 'Site Start URL'));

        /** @var \MODX\Revolution\modContext $context */
        foreach ($contexts as $context) {
            $key = $context->get('key');

            // Prepare the context
            $context->prepare();

            // Get the site URL
            $siteUrl = $context->getOption('site_url', '');

            // Get the site start URL
            $siteStartId = $context->getOption('site_start', 0);
            $siteStartUrl = '';

            if ($siteStartId) {
                /** @var \MODX\Revolution\modResource $resource */
                $resource = $this->modx->getObject('modResource', $siteStartId);
                if ($resource) {
                    $siteStartUrl = $this->modx->makeUrl($siteStartId, $key, '', 'full');
                }
            }

            $table->addRow(array($key, $siteUrl, $siteStartUrl));
        }

        $table->render();

        return 0;
    }
}
