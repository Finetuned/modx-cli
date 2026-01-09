<?php

namespace MODX\CLI\Command\Extra;

use MODX\CLI\Command\BaseCmd;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to add a component to MODX
 */
class AddComponent extends BaseCmd
{
    const MODX = true;

    protected $name = 'extra:add-component';
    protected $description = 'Add a component to MODX';
    protected $jsonOutput = false;
    protected $actions = array();

    protected function getArguments()
    {
        return array(
            array(
                'namespace',
                InputArgument::REQUIRED,
                'The namespace of the component'
            ),
        );
    }

    protected function getOptions()
    {
        return array_merge(parent::getOptions(), array(
            array(
                'path',
                null,
                InputOption::VALUE_REQUIRED,
                'The path of the component'
            ),
            array(
                'assets_path',
                null,
                InputOption::VALUE_REQUIRED,
                'The assets path of the component'
            ),
            array(
                'force',
                'f',
                InputOption::VALUE_NONE,
                'Force creation without confirmation'
            ),
        ));
    }

    protected function process()
    {
        $this->jsonOutput = (bool) $this->option('json');
        $this->actions = array();
        $namespace = $this->argument('namespace');
        $namespaceExists = false;

        // Check if the namespace already exists
        $ns = $this->modx->getObject(\MODX\Revolution\modNamespace::class, $namespace);
        if ($ns) {
            $namespaceExists = true;
            if (!$this->option('force')) {
                if (!$this->confirm("Namespace '{$namespace}' already exists. Do you want to update it?")) {
                    $this->outputResult(false, 'Operation aborted', array(
                        'namespace' => $namespace,
                        'updated' => true,
                    ));
                    return 0;
                }
            }
        } else {
            $ns = $this->modx->newObject(\MODX\Revolution\modNamespace::class);
            $ns->set('name', $namespace);
        }

        // Set the path
        $path = $this->option('path');
        if (!$path) {
            $path = "components/{$namespace}/";
        }

        // Make sure the path ends with a trailing slash
        if (substr($path, -1) !== '/') {
            $path .= '/';
        }

        $ns->set('path', $path);

        // Set the assets path
        $assetsPath = $this->option('assets_path');
        if (!$assetsPath) {
            $assetsPath = "assets/components/{$namespace}/";
        }

        // Make sure the assets path ends with a trailing slash
        if (substr($assetsPath, -1) !== '/') {
            $assetsPath .= '/';
        }

        $ns->set('assets_path', $assetsPath);

        // Save the namespace
        if ($ns->save()) {
            $this->emitInfo("Namespace '{$namespace}' saved successfully");

            // Create the component directories
            $basePath = $this->modx->getOption('base_path');
            $directories = array(
                $basePath . $path,
                $basePath . $path . 'controllers/',
                $basePath . $path . 'elements/',
                $basePath . $path . 'elements/chunks/',
                $basePath . $path . 'elements/plugins/',
                $basePath . $path . 'elements/snippets/',
                $basePath . $path . 'elements/templates/',
                $basePath . $path . 'model/',
                $basePath . $assetsPath,
                $basePath . $assetsPath . 'css/',
                $basePath . $assetsPath . 'js/',
                $basePath . $assetsPath . 'img/',
            );

            foreach ($directories as $directory) {
                if (!file_exists($directory)) {
                    if (mkdir($directory, 0755, true)) {
                        $this->emitInfo("Created directory: {$directory}");
                    } else {
                        $this->emitError("Failed to create directory: {$directory}");
                    }
                }
            }

            // Create the index.php controller
            $controllerPath = $basePath . $path . 'controllers/index.php';
            if (!file_exists($controllerPath)) {
                $content = <<<EOT
<?php
/**
 * {$namespace} controller
 *
 * @package {$namespace}
 */
class {$namespace}IndexManagerController extends modExtraManagerController {
    public function getPageTitle() {
        return '{$namespace}';
    }
    
    public function loadCustomCssJs() {
        \$this->addCss(\$this->modx->getOption('assets_url') . 'components/{$namespace}/css/mgr.css');
        \$this->addJavascript(\$this->modx->getOption('assets_url') . 'components/{$namespace}/js/mgr.js');
    }
    
    public function getTemplateFile() {
        return \$this->modx->getOption('core_path') . 'components/{$namespace}/templates/index.tpl';
    }
}
EOT;

                if (file_put_contents($controllerPath, $content)) {
                    $this->emitInfo("Created controller: {$controllerPath}");
                } else {
                    $this->emitError("Failed to create controller: {$controllerPath}");
                }
            }

            // Create the template file
            $templateDir = $basePath . $path . 'templates/';
            if (!file_exists($templateDir)) {
                mkdir($templateDir, 0755, true);
            }

            $templatePath = $templateDir . 'index.tpl';
            if (!file_exists($templatePath)) {
                $content = <<<EOT
<div id="{$namespace}-panel">
    <h2>{$namespace}</h2>
    <p>This is the {$namespace} component.</p>
</div>
EOT;

                if (file_put_contents($templatePath, $content)) {
                    $this->emitInfo("Created template: {$templatePath}");
                } else {
                    $this->emitError("Failed to create template: {$templatePath}");
                }
            }

            // Create the CSS file
            $cssPath = $basePath . $assetsPath . 'css/mgr.css';
            if (!file_exists($cssPath)) {
                $content = <<<EOT
#{$namespace}-panel {
    padding: 20px;
}
EOT;

                if (file_put_contents($cssPath, $content)) {
                    $this->emitInfo("Created CSS file: {$cssPath}");
                } else {
                    $this->emitError("Failed to create CSS file: {$cssPath}");
                }
            }

            // Create the JS file
            $jsPath = $basePath . $assetsPath . 'js/mgr.js';
            if (!file_exists($jsPath)) {
                $content = <<<EOT
// {$namespace} manager JS
EOT;

                if (file_put_contents($jsPath, $content)) {
                    $this->emitInfo("Created JS file: {$jsPath}");
                } else {
                    $this->emitError("Failed to create JS file: {$jsPath}");
                }
            }

            // Create a menu for the component
            $menu = $this->modx->getObject(\MODX\Revolution\modMenu::class, array(
                'namespace' => $namespace,
                'action' => 'index',
            ));

            if (!$menu) {
                $menu = $this->modx->newObject(\MODX\Revolution\modMenu::class);
                $menu->fromArray(array(
                    'namespace' => $namespace,
                    'action' => 'index',
                    'parent' => 'components',
                    'text' => $namespace,
                    'description' => "The {$namespace} component",
                    'icon' => '',
                    'menuindex' => 0,
                    'params' => '',
                    'handler' => '',
                ));

                if ($menu->save()) {
                    $this->emitInfo("Created menu for {$namespace}");
                } else {
                    $this->emitError("Failed to create menu for {$namespace}");
                }
            }

            $this->outputResult(true, "Component '{$namespace}' created successfully", array(
                'namespace' => $namespace,
                'path' => $path,
                'assets_path' => $assetsPath,
                'updated' => $namespaceExists,
            ));
        } else {
            $this->outputResult(false, "Failed to save namespace '{$namespace}'", array(
                'namespace' => $namespace,
                'path' => $path,
                'assets_path' => $assetsPath,
                'updated' => $namespaceExists,
            ));
        }

        return 0;
    }

    protected function emitInfo($message)
    {
        if ($this->jsonOutput) {
            $this->actions[] = array('success' => true, 'message' => $message);
            return;
        }

        $this->info($message);
    }

    protected function emitError($message)
    {
        if ($this->jsonOutput) {
            $this->actions[] = array('success' => false, 'message' => $message);
            return;
        }

        $this->error($message);
    }

    protected function outputResult($success, $message, array $payload = array())
    {
        if ($this->jsonOutput) {
            $this->output->writeln(json_encode(array_merge(array(
                'success' => (bool) $success,
                'message' => $message,
                'actions' => $this->actions,
            ), $payload), JSON_PRETTY_PRINT));
        } else {
            if ($success) {
                $this->info($message);
            } else {
                $this->error($message);
            }
        }
    }
}
