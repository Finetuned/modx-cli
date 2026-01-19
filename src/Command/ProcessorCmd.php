<?php

namespace MODX\CLI\Command;

use MODX\CLI\Configuration\FieldMappings;
use MODX\CLI\Messages\ErrorMessages;
use Symfony\Component\Console\Input\InputOption;

/**
 * A processor based command
 */
abstract class ProcessorCmd extends BaseCmd
{
    public const MODX = true;

    /**
     * A processor path
     *
     * @var string
     */
    protected $processor;

    protected $defaultsOptions = [];
    protected $defaultsProperties = [];

    /**
     * An array of columns to be used in tables output
     *
     * @var array
     */
    protected $headers = [];

    /**
     * An array of required arguments to be set as processor properties (parameters)
     *
     * @var array
     */
    protected $required = [];

    /**
     * The processor response
     *
     * @var \MODX\Revolution\Processors\ProcessorResponse
     */
    protected $response;

    /**
     * Execute the command.
     *
     * @return integer
     */
    protected function process()
    {
        $properties = array_merge(
            $this->defaultsProperties,
            $this->processArray('properties')
        );

        $options = array_merge(
            $this->defaultsOptions,
            $this->processArray('options')
        );

        // Place required fields into the properties to be sent to the processor
        if (!empty($this->required)) {
            foreach ($this->required as $field) {
                $properties[$field] = $this->argument($field);
            }
        }

        // Allow "on the fly" columns addition/removal
        $this->handleColumns();

        // Allow the command to break if some criteria aren't met
        if ($this->beforeRun($properties, $options) === false) {
            $this->info('Operation aborted');
            return 0; // Return 0 for success
        }

        /** @var \MODX\Revolution\Processors\ProcessorResponse $response */
        $response = $this->modx->runProcessor($this->processor, $properties, $options);

        if (!($response instanceof \MODX\Revolution\Processors\ProcessorResponse) || !$response->getResponse()) {
            $this->output->writeln('<error>' . ErrorMessages::get(ErrorMessages::PROCESSOR_FAILED) . '</error>');
            $this->output->writeln('<error>' . $response->getMessage() . '</error>');
            return 1; // Return non-zero for failure
        }

        // Trick for "list" processors not returning "success"
        if ($response->isError() && isset($response->response['success']) && !$response->response['success']) {
            $errors = $response->getFieldErrors();

            // Check for --json flag
            if ($this->option('json')) {
                $errorData = ['success' => false];
                if (empty($errors)) {
                    $errorData['message'] = $response->getMessage();
                } else {
                    $errorData['errors'] = array_map(function ($e) {
                        return ['field' => $e->field, 'message' => $e->message];
                    }, $errors);
                }
                $this->output->writeln(json_encode($errorData, JSON_PRETTY_PRINT));
            } else {
                // Plain text output
                if (empty($errors)) {
                    // No field-specific errors, output general error message
                    $this->output->writeln('<error>' . $response->getMessage() . '</error>');
                } else {
                    // Output field-specific errors
                    foreach ($errors as $e) {
                        $this->output->writeln('<error>' . $e->field . ' : ' . $e->message . '</error>');
                    }
                }
            }
            return 1; // Return non-zero for failure
        }

        $this->response =& $response;

        $result = $this->processResponse($this->decodeResponse($response));

        // If processResponse() doesn't return anything, return 0 for success
        if ($result === null) {
            return 0;
        }

        return $result;
    }

    /**
     * Command result logic
     *
     * @param array $response The decoded processor response.
     * @return integer
     */
    protected function processResponse(array $response = [])
    {
        if ($this->option('json')) {
            $this->output->writeln(json_encode($response, JSON_PRETTY_PRINT));
            return 0;
        }

        $this->info('Override me to process the processor response');
        return 0; // Return 0 for success
    }

    /**
     * A method to implement before running the processor. Return false to break execution.
     *
     * @param array $properties The properties which will be sent to the processor.
     * @param array $options    The options which will be sent to the processor.
     *
     * @return mixed Return false here to break the command execution.
     */
    protected function beforeRun(array &$properties = [], array &$options = [])
    {
    }

    /**
     * Fetch an existing MODX object by ID and class
     *
     * @param string         $class The MODX object class name.
     * @param integer|string $id    The object ID or unique key.
     * @return \xPDO\Om\xPDOObject|null
     */
    protected function getExistingObject(string $class, int|string $id)
    {
        if (!$this->modx) {
            return null;
        }

        return $this->modx->getObject($class, $id);
    }

    /**
     * Pre-populate properties with existing object data for update operations
     * This prevents MODX processors from requiring fields that shouldn't be required for updates
     *
     * @param array          $properties The properties array to populate.
     * @param string         $class      The MODX object class name.
     * @param integer|string $id         The object ID or unique key.
     * @param array          $fieldMap   Optional mapping of property names to object field names.
     * @return boolean True if object was found and properties populated, false otherwise.
     */
    protected function prePopulateFromExisting(
        array &$properties,
        string $class,
        int|string $id,
        array $fieldMap = []
    ) {
        $object = $this->getExistingObject($class, $id);
        if (!$object) {
            return false;
        }

        // Use provided field map or get from FieldMappings configuration
        $mapping = !empty($fieldMap) ? $fieldMap : FieldMappings::get($class);

        // Pre-populate properties with existing values if not already set
        foreach ($mapping as $propertyName => $fieldName) {
            if (!array_key_exists($propertyName, $properties) || $properties[$propertyName] === null) {
                $value = $object->get($fieldName);
                // Only set the property if the value is not null and not an empty string
                if ($value !== null && $value !== '') {
                    $properties[$propertyName] = $value;
                }
            }
        }

        return true;
    }

    /**
     * Handle default values for create operations
     * Ensures default values are properly applied when options are provided
     *
     * @param array $properties The properties array to populate.
     * @param array $defaults   Array of default values.
     */
    protected function applyDefaults(array &$properties, array $defaults = [])
    {
        foreach ($defaults as $key => $defaultValue) {
            // Only apply default if the property is not already set
            if (!array_key_exists($key, $properties) || $properties[$key] === null) {
                // Check if there's an option for this property
                $optionValue = $this->option($key);
                if ($optionValue !== null) {
                    $properties[$key] = $optionValue;
                } else {
                    $properties[$key] = $defaultValue;
                }
            }
        }
    }

    /**
     * Safely add options to properties, handling type conversion and validation
     *
     * @param array $properties The properties array to populate.
     * @param array $optionKeys Array of option keys to process.
     * @param array $typeMap    Optional type mapping for conversion (e.g., 'published' => 'boolean').
     */
    protected function addOptionsToProperties(array &$properties, array $optionKeys, array $typeMap = [])
    {
        foreach ($optionKeys as $key) {
            $value = $this->option($key);
            if ($value !== null) {
                // Apply type conversion if specified
                if (isset($typeMap[$key])) {
                    switch ($typeMap[$key]) {
                        case 'boolean':
                        case 'bool':
                            $value = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                            if ($value === null) {
                                $value = (int) $this->option($key); // Fallback to integer conversion
                            } else {
                                $value = (int) $value; // Convert boolean to integer for MODX
                            }
                            break;
                        case 'integer':
                        case 'int':
                            $value = (int) $value;
                            break;
                        case 'float':
                            $value = (float) $value;
                            break;
                    }
                }
                $properties[$key] = $value;
            }
        }
    }

    /**
     * Decode the processor response if json encoded
     *
     * @param \MODX\Revolution\Processors\ProcessorResponse $response The processor response.
     *
     * @return array|mixed
     */
    protected function decodeResponse(\MODX\Revolution\Processors\ProcessorResponse &$response)
    {
        $results = $response->getResponse();
        if (!is_array($results)) {
            $results = json_decode($results, true);
        }

        return $results;
    }

    /**
     * Process an array value (option/argument)
     *
     * @param string $key  The argument/option name.
     * @param string $type Argument or option.
     *
     * @return array
     */
    protected function processArray(string $key, string $type = 'option')
    {
        $result = [];
        foreach ($this->$type($key) as $data) {
            $exp = explode('=', $data);

            $result[trim($exp[0])] = trim($exp[1]);
        }

        return $result;
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
                'properties',
                'p',
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'An array of properties to be sent to the processor, ' .
                "i.e. --properties='key=value' --properties='another_key=value'"
            ],
            [
                'options',
                'o',
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'An array of options to be sent to the processor, ' .
                "i.e. --options='processors_path=value' --options='location=value'"
            ],
            // Tables related
            [
                'unset',
                'u',
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'An array of columns to hide from the results table, ' .
                'i.e. --unset=id --unset=name'
            ],
            [
                'add',
                'a',
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'An array of columns to add to the results table, ' .
                "i.e. --add=column -a'other_column'"
            ],
        ]);
    }

    // Tables related.

    /**
     * Allow on-the-fly table column addition or removal.
     *
     * @return void
     */
    protected function handleColumns()
    {
        // Support columns "removal"
        $unset = $this->option('unset');
        if (!empty($unset)) {
            foreach ($unset as $k) {
                if (in_array($k, $this->headers)) {
                    $idx = array_search($k, $this->headers);
                    if ($idx !== false) {
                        unset($this->headers[$idx]);
                    }
                }
            }
        }

        // Support columns "addition"
        $add = $this->option('add');
        if (!empty($add)) {
            foreach ($add as $k) {
                if (!in_array($k, $this->headers)) {
                    $this->headers[] = $k;
                }
            }
        }
    }

    /**
     * Grab the appropriate columns for the given record
     *
     * @param array $record The record to format.
     *
     * @return array Usable row for the table output.
     */
    protected function processRow(array $record = [])
    {
        $result = [];
        foreach ($this->headers as $k) {
            if (!array_key_exists($k, $record)) {
                $result[] = '';
                continue;
            }
            $value = $record[$k];
            $result[] = $this->parseValue($value, $k);
        }

        return $result;
    }

    /**
     * Allow raw values to be "formatted"
     *
     * @param mixed  $value  The raw value.
     * @param string $column The column name.
     *
     * @return mixed
     */
    protected function parseValue(mixed $value, string $column)
    {
        $method = 'format' . ucfirst($column);
        if (method_exists($this, $method)) {
            return $this->$method($value);
        }

        return $value;
    }

    /**
     * A "formatter" method to display booleans as "Yes/No" instead of "1/0"
     *
     * @param mixed $value The raw value.
     *
     * @return string
     */
    protected function renderBoolean(mixed $value)
    {
        $result = 'No';
        if ($value) {
            $result = 'Yes';
        }

        return $result;
    }

    /**
     * Retrieve a column value for the given object
     *
     * @param string $class  The object class.
     * @param mixed  $pk     The primary key or criteria to grab the object.
     * @param string $column The desired column value.
     *
     * @return mixed Either the column value if found, or the given primary key.
     */
    protected function renderObject(string $class, mixed $pk, string $column)
    {
        if ($pk && $pk != '0') {
            /** @var \xPDO\Om\xPDOObject $object */
            $object = $this->modx->getObject($class, $pk);
            if ($object instanceof \xPDO\Om\xPDOObject) {
                return $object->get($column);
            }
        }

        return $pk;
    }
}
