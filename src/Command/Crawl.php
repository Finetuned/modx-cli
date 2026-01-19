<?php

namespace MODX\CLI\Command;

use MODX\CLI\Command\BaseCmd;
use MODX\Revolution\modResource;
use Symfony\Component\Console\Input\InputArgument;
use xPDO\Om\xPDOQuery;

/**
 * A command to crawl resources (to cache them)
 */
class Crawl extends BaseCmd
{
    public const MODX = true;

    protected $name = 'crawl';
    protected $description = 'Crawl resources to prime their caches';

    protected $curl;
    protected $start;
    protected $jsonOutput = false;
    protected $crawlResults = [];
    protected $crawlErrors = [];

    /**
     * Execute the command.
     *
     * @return integer
     */
    protected function process()
    {
        $from = $this->argument('from');
        $this->jsonOutput = (bool) $this->option('json');
        $this->crawlResults = [];
        $this->crawlErrors = [];

        try {
            $c = $this->getCriteria($from);

            $total = $this->modx->getCount(modResource::class, $c);
            if ($total > 0) {
                if (!$this->prepareCurl()) {
                    $this->outputResult(false, 'Failed to initialize cURL');
                    return 1;
                }
            } else {
                $this->outputResult(true, 'No resources to crawl found with criteria', [
                    'total' => 0,
                ]);
                return 0;
            }

            if (!$this->jsonOutput) {
                $this->comment("\n<info>{$total}</info> 'root' resources found");
            }

            $collection = $this->modx->getCollection(modResource::class, $c);
            $context = '';
            /** @var \MODX\Revolution\modResource $resource */
            foreach ($collection as $resource) {
                $contextKey = (string) $resource->get('context_key');
                $resourceId = (int) $resource->get('id');
                if ($context !== $contextKey) {
                    if (!$this->jsonOutput) {
                        $this->comment("\nProcessing context {$contextKey}");
                    }
                    $this->modx->switchContext($contextKey);
                    $context = $contextKey;
                    //$this->modx->context->setOption('session_enabled', false);
                }
                $this->crawl($resourceId);

                if (is_numeric($from)) {
                    // Process children too
                    $children = $this->modx->getChildIds($resourceId, 999);
                    foreach ($children as $id) {
                        $this->crawl($id);
                    }
                }
            }

            if (is_numeric($from)) {
                // Crawl the container too
                $this->crawl((int) $from);
            }

            if ($this->curl) {
                curl_close($this->curl);
            }
            $duration = microtime(true) - $this->start;
            if ($this->jsonOutput) {
                $this->outputResult(true, 'Crawl completed', [
                    'total' => $total,
                    'results' => $this->crawlResults,
                    'errors' => $this->crawlErrors,
                    'duration' => $duration,
                ]);
            } else {
                $this->line("\n" . sprintf("Executed in <info>%2.4f</info> seconds", $duration));
            }

            return 0;
        } catch (\Exception $e) {
            $this->outputResult(false, 'Crawl failed: ' . $e->getMessage(), [
                'errors' => $this->crawlErrors,
            ]);
            if ($this->curl) {
                curl_close($this->curl);
            }
            return 1;
        }
    }

    /**
     * Build the query
     *
     * @param string $from Either the context key (string) or a resource id (int) to grab the resources to crawl from.
     *
     * @return xPDOQuery
     */
    protected function getCriteria(string $from): xPDOQuery
    {
        $criteria = [
            'published' => true,
            'deleted' => false,
            'cacheable' => true,
        ];
        if (is_numeric($from)) {
            // From a given resource container
            $criteria['parent'] = (int) $from;
        } else {
            if ($from === 'all') {
                // We want all contexts
                $criteria['context_key:!='] = 'mgr';
            } else {
                // A single context
                $criteria['context_key'] = $from;
            }
        }

        $c = $this->modx->newQuery(modResource::class);
        $c->select('id,pagetitle,context_key');
        $c->where($criteria);
        $c->sortby('context_key');

        return $c;
    }

    /**
     * Perform the request to the given resource ID
     *
     * @param integer $id The id.
     */
    protected function crawl(int $id)
    {
        $url = $this->modx->makeUrl($id, '', '', 'full');
        curl_setopt($this->curl, CURLOPT_URL, $url);
        curl_exec($this->curl);
        $status = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);
        if ($status > 400) {
            $status = "<error>{$status}</error>";
        }
        $entry = null;
        if ($this->jsonOutput) {
            $entry = [
                'id' => $id,
                'url' => $url,
                'status' => curl_getinfo($this->curl, CURLINFO_HTTP_CODE),
            ];
        } else {
            $this->line("Requested <info>{$url}</info> (<comment>{$id}</comment>) - {$status}");
        }

        if (curl_errno($this->curl)) {
            $error = 'cURL error: ' . curl_errno($this->curl) . ' - ' . curl_error($this->curl);
            if ($this->jsonOutput) {
                $entry['error'] = $error;
                $this->crawlErrors[] = $error;
            } else {
                $this->error($error);
            }
        }

        if ($this->jsonOutput && $entry !== null) {
            $this->crawlResults[] = $entry;
        }
    }

    /**
     * Prepare cURL handler
     *
     * @return boolean True if cURL was initialized successfully, false otherwise.
     */
    protected function prepareCurl(): bool
    {
        $this->start = microtime(true);

        if (!function_exists('curl_init')) {
            $this->outputResult(false, 'cURL extension is not available');
            return false;
        }

        $ch = curl_init();
        if ($ch === false) {
            $this->outputResult(false, 'Failed to initialize cURL');
            return false;
        }

        $result = curl_setopt_array($ch, [
            CURLOPT_NOBODY => true,
            CURLOPT_FAILONERROR => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_RETURNTRANSFER => true,

            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_HEADER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            //CURLOPT_USERAGENT => "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)"
            //CURLOPT_FRESH_CONNECT => true,

//            CURLOPT_COOKIEFILE => '/tmp/cookie.txt',
//            CURLOPT_COOKIEJAR => '/tmp/cookie.txt',
        ]);

        if (!$result) {
            $this->outputResult(false, 'Failed to set cURL options');
            curl_close($ch);
            return false;
        }

        $this->curl = $ch;
        return true;
    }

    /**
     * Output the result payload.
     *
     * @param boolean $success Whether the operation succeeded.
     * @param string  $message The message to display.
     * @param array   $payload Additional payload data.
     * @return void
     */
    protected function outputResult(bool $success, string $message, array $payload = []): void
    {
        if ($this->jsonOutput) {
            $this->output->writeln(json_encode(array_merge([
                'success' => (bool) $success,
                'message' => $message,
            ], $payload), JSON_PRETTY_PRINT));
        } else {
            if ($success) {
                $this->comment($message);
            } else {
                $this->error($message);
            }
        }
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            [
                'from',
                InputArgument::REQUIRED,
                'The context key or resource ID to crawl from. Use "all" to process all web contexts.'
            ],
        ];
    }
}
