<?php

namespace MODX\CLI\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Global search across all content in the MODX instance including resources,
 * elements (chunks, templates, TVs, snippets, plugins), and users.
 *
 * This is the same search functionality as the "uberbar" quick search in the MODX Manager.
 */
class Find extends ListProcessor
{
    protected $processor = 'Search\Search';
    protected $headers = [
        'name', 'type', 'description'
    ];

    protected $required = [
        'query'
    ];

    protected $name = 'find';
    protected $description = 'Search across all content in the MODX instance (resources, elements, users)';

    protected $typeFilter = null;
    protected $contextFilter = null;
    protected $searchInContent = true;

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setAliases(['search']);
    }

    /**
     * Prepare properties before running the processor.
     *
     * @param array $properties The processor properties.
     * @param array $options    The processor options.
     * @return boolean|null Return false to abort.
     */
    protected function beforeRun(array &$properties = [], array &$options = [])
    {
        $data = $this->modx->getVersionData();
        $version = $data['full_version'];
        if (version_compare($version, '2.3.0', '<')) {
            $this->error('This MODX version does not support that search function');
            return false;
        }

        // Store filter options for post-processing
        $this->typeFilter = $this->option('type');
        $this->contextFilter = $this->option('context');
        $this->searchInContent = !$this->option('no-content');

        // Set the query property
        $properties['query'] = $this->argument('query');

        return null;
    }

    /**
     * Handle the processor response with custom filtering.
     *
     * @param array $results The processor response.
     * @return integer
     */
    protected function processResponse(array $results = [])
    {
        $items = $results['results'] ?? [];

        // Apply custom filtering
        $items = $this->applyFilters($items);

        $total = count($items);

        if ($this->option('json')) {
            $output = [
                'total' => $total,
                'results' => $items
            ];
            $this->output->writeln(json_encode($output, JSON_PRETTY_PRINT));
            return 0;
        }

        $this->renderBody($items);

        return 0;
    }

    /**
     * Apply filters to search results.
     *
     * @param array $results The processor results.
     * @return array Filtered and formatted results.
     */
    protected function applyFilters(array $results)
    {
        $filtered = $results;

        // Apply type filtering if specified
        if ($this->typeFilter) {
            $filtered = $this->filterByType($filtered, $this->typeFilter);
        }

        // Apply context filtering for resources if specified
        if ($this->contextFilter) {
            $filtered = $this->filterByContext($filtered, $this->contextFilter);
        }

        // Apply limit if specified
        $limit = (int) $this->option('limit');
        if ($limit > 0 && count($filtered) > $limit) {
            $filtered = array_slice($filtered, 0, $limit);
        }

        return $filtered;
    }

    /**
     * Filter results by type.
     *
     * @param array  $results The results to filter.
     * @param string $type    The type to filter by.
     * @return array Filtered results.
     */
    protected function filterByType(array $results, string $type)
    {
        $type = strtolower($type);

        // Handle 'element' as a special case to include all element types
        if ($type === 'element') {
            $elementTypes = ['chunks', 'templates', 'tvs', 'snippets', 'plugins'];
            return array_filter($results, function ($result) use ($elementTypes) {
                return in_array($result['type'], $elementTypes);
            });
        }

        // Add 's' to make it plural if not already (to match processor output)
        $typeToMatch = (substr($type, -1) === 's') ? $type : $type . 's';

        return array_filter($results, function ($result) use ($typeToMatch) {
            return $result['type'] === $typeToMatch;
        });
    }

    /**
     * Filter resource results by context.
     *
     * @param array  $results The results to filter.
     * @param string $context The context to filter by.
     * @return array Filtered results.
     */
    protected function filterByContext(array $results, string $context)
    {
        // This filter only applies to resources
        // We need to fetch additional data for resources to check context
        return array_filter($results, function ($result) use ($context) {
            if ($result['type'] !== 'resources') {
                return true; // Keep non-resource results
            }

            // Extract resource ID from _action field
            if (isset($result['_action']) && preg_match('/id=(\d+)/', $result['_action'], $matches)) {
                $resourceId = (int) $matches[1];
                $resource = $this->modx->getObject(\MODX\Revolution\modResource::class, $resourceId);
                if ($resource) {
                    return $resource->get('context_key') === $context;
                }
            }

            return false;
        });
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
                'query',
                InputArgument::REQUIRED,
                'The search query (searches in names, descriptions, and content)'
            ],
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array_merge(parent::getOptions(), [
            [
                'type',
                null,
                InputOption::VALUE_REQUIRED,
                'Filter by type: resource, chunk, template, tv, snippet, plugin, user, or element (all elements)'
            ],
            [
                'context',
                null,
                InputOption::VALUE_REQUIRED,
                'Filter resources by context (only applies to resource results)'
            ],
            [
                'no-content',
                null,
                InputOption::VALUE_NONE,
                'Skip searching in content fields (faster, searches only names and descriptions)'
            ],
        ]);
    }
}
