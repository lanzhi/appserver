<?php
/**
 * AppserverIo\Appserver\Core\Api\Node\AppserverNode
 *
 * PHP version 5
 *
 * @category   Server
 * @package    Appserver
 * @subpackage Application
 * @author     Tim Wagner <tw@appserver.io>
 * @copyright  2014 TechDivision GmbH <info@appserver.io>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
 */

namespace AppserverIo\Appserver\Core\Api\Node;

use Psr\Log\LogLevel;
use AppserverIo\Logger\LoggerUtils;

/**
 * DTO to transfer the application server's complete configuration.
 *
 * @category   Server
 * @package    Appserver
 * @subpackage Application
 * @author     Tim Wagner <tw@appserver.io>
 * @copyright  2014 TechDivision GmbH <info@appserver.io>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
 */
class AppserverNode extends AbstractNode
{

    /**
     * A params node trait.
     *
     * @var \TraitInterface
     */
    use ParamsNodeTrait;

    /**
     * The node containing information about the base directory.
     *
     * @var \AppserverIo\Appserver\Core\Api\Node\BaseDirectoryNode @AS\Mapping(nodeName="baseDirectory", nodeType="AppserverIo\Appserver\Core\Api\Node\BaseDirectoryNode")
     */
    protected $baseDirectory;

    /**
     * The node containing information about the initial context.
     *
     * @var \AppserverIo\Appserver\Core\Api\Node\InitialContextNode @AS\Mapping(nodeName="initialContext", nodeType="AppserverIo\Appserver\Core\Api\Node\InitialContextNode")
     */
    protected $initialContext;

    /**
     * Array with nodes for the registered loggers.
     *
     * @var array @AS\Mapping(nodeName="loggers/logger", nodeType="array", elementType="AppserverIo\Appserver\Core\Api\Node\LoggerNode")
     */
    protected $loggers = array();

    /**
     * Array with nodes for the registered extractors.
     *
     * @var array @AS\Mapping(nodeName="extractors/extractor", nodeType="array", elementType="AppserverIo\Appserver\Core\Api\Node\ExtractorNode")
     */
    protected $extractors = array();

    /**
     * Array with nodes for the registered provisioners.
     *
     * @var array @AS\Mapping(nodeName="provisioners/provisioner", nodeType="array", elementType="AppserverIo\Appserver\Core\Api\Node\ProvisionerNode")
     */
    protected $provisioners = array();

    /**
     * Array with nodes for the registered containers.
     *
     * @var array @AS\Mapping(nodeName="containers/container", nodeType="array", elementType="AppserverIo\Appserver\Core\Api\Node\ContainerNode")
     */
    protected $containers = array();

    /**
     * Array with the information about the deployed applications.
     *
     * @var array @AS\Mapping(nodeName="apps/app", nodeType="array", elementType="AppserverIo\Appserver\Core\Api\Node\AppNode")
     */
    protected $apps = array();

    /**
     * Array with nodes for the registered datasources.
     *
     * @var array @AS\Mapping(nodeName="datasources/datasource", nodeType="array", elementType="AppserverIo\Appserver\Core\Api\Node\DatasourceNode")
     */
    protected $datasources = array();

    /**
     * Array with nodes for the registered scanners.
     *
     * @var array @AS\Mapping(nodeName="scanners/scanner", nodeType="array", elementType="AppserverIo\Appserver\Core\Api\Node\ScannerNode")
     */
    protected $scanners = array();

    /**
     * Initializes the node with default values.
     */
    public function __construct()
    {
        // initialize the default configuration
        $this->initDefaultLoggers();
        $this->initDefaultScanners();
        $this->initDefaultExtractors();
        $this->initDefaultProvisioners();
        $this->initDefaultInitialContext();
    }

    /**
     * Initializes the default initial context configuration.
     *
     * @return void
     */
    public function initDefaultInitialContext()
    {

        // initialize the configuration values for the initial context
        $description = new DescriptionNode(new NodeValue('The initial context configuration.'));
        $classLoader = new ClassLoaderNode('default', 'ClassLoaderInterface', 'AppserverIo\Appserver\Core\SplClassLoader');
        $storage = new StorageNode('AppserverIo\Storage\StackableStorage');

        // set the default initial context configuration
        $this->initialContext = new InitialContextNode('AppserverIo\Appserver\Core\InitialContext', $description, $classLoader, $storage);
    }

    /**
     * Initializes the default scanners for archive based deployment.
     *
     * @return void
     */
    protected function initDefaultScanners()
    {

        // initialize the params for the deployment scanner
        $scannerParams = array();
        $intervalParam = new ParamNode('interval', 'integer', new NodeValue(1));
        $extensionsToWatchParam = new ParamNode('extendsToWatch', 'string', new NodeValue('dodeploy, deployed'));
        $scannerParams[$intervalParam->getPrimaryKey()] = $intervalParam;
        $scannerParams[$extensionsToWatchParam->getPrimaryKey()] = $extensionsToWatchParam;

        // initialize the directories to scan
        $directories = array(new DirectoryNode(new NodeValue('deploy')));

        // initialize the deployment scanner
        $deploymentScanner = new ScannerNode('deployment', 'AppserverIo\Appserver\Core\Scanner\DeploymentScanner', $scannerParams, $directories);

        // add scanner to the appserver node
        $this->scanners[$deploymentScanner->getPrimaryKey()] = $deploymentScanner;
    }

    /**
     * Initializes the default extractors for archive based deployment.
     *
     * @return void
     */
    protected function initDefaultExtractors()
    {

        // initialize the extractor
        $pharExtractor = new ExtractorNode('phar', 'AppserverIo\Appserver\Core\Extractors\PharExtractor');

        // add extractor to the appserver node
        $this->extractors[$pharExtractor->getPrimaryKey()] = $pharExtractor;
    }

    /**
     * Initializes the default provisioners for database and
     * application database relation.
     *
     * @return void
     */
    protected function initDefaultProvisioners()
    {

        // initialize the provisioners
        $datasourceProvisioner = new ProvisionerNode('datasource', 'AppserverIo\Appserver\Core\DatasourceProvisioner');
        $standardProvisioner = new ProvisionerNode('standard', 'AppserverIo\Appserver\Core\StandardProvisioner');

        // add the provisioners to the appserver node
        $this->provisioners[$datasourceProvisioner->getPrimaryKey()] = $datasourceProvisioner;
        $this->provisioners[$standardProvisioner->getPrimaryKey()] = $standardProvisioner;
    }

    /**
     * Initializes the default logger configuration.
     *
     * @return void
     */
    protected function initDefaultLoggers()
    {

        // we dont need any processors
        $processors = array();

        // initialize the params for the system logger handler
        $handlerParams = array();
        $logLevelParam = new ParamNode('logLevel', 'string', new NodeValue(LogLevel::INFO));
        $logFileParam = new ParamNode('logFile', 'string', new NodeValue('var/log/appserver-errors.log'));
        $handlerParams[$logFileParam->getPrimaryKey()] = $logFileParam;
        $handlerParams[$logLevelParam->getPrimaryKey()] = $logLevelParam;

        // initialize the handler
        $handlers = array();
        $handler = new HandlerNode('\AppserverIo\Logger\Handlers\CustomFileHandler', null, $handlerParams);
        $handlers[$handler->getPrimaryKey()] = $handler;

        // initialize the system logger with the processor and the handlers
        $systemLogger = new LoggerNode(LoggerUtils::SYSTEM, '\AppserverIo\Logger\Logger', 'system', $processors, $handlers);

        // we dont need any processors
        $processors = array();

        // initialize the params for the access logger formatter
        $formatterParams = array();
        $messageFormatParam = new ParamNode('format', 'string', new NodeValue('%4$s'));
        $formatterParams[$messageFormatParam->getPrimaryKey()] = $messageFormatParam;

        // initialize the formatter for the access logger
        $formatter = new FormatterNode('\AppserverIo\Logger\Formatters\StandardFormatter', $formatterParams);

        // initialize the params for the system logger handler
        $handlerParams = array();
        $logLevelParam = new ParamNode('logLevel', 'string', new NodeValue(LogLevel::DEBUG));
        $logFileParam = new ParamNode('logFile', 'string', new NodeValue('var/log/appserver-access.log'));
        $handlerParams[$logFileParam->getPrimaryKey()] = $logFileParam;
        $handlerParams[$logLevelParam->getPrimaryKey()] = $logLevelParam;

        // initialize the handler
        $handlers = array();
        $handler = new HandlerNode('\AppserverIo\Logger\Handlers\CustomFileHandler', $formatter, $handlerParams);
        $handlers[$handler->getPrimaryKey()] = $handler;

        // initialize the system logger with the processor and the handlers
        $accessLogger = new LoggerNode(LoggerUtils::ACCESS, '\AppserverIo\Logger\Logger', 'access', $processors, $handlers);

        // add the loggers to the default logger configuration
        $this->loggers[$systemLogger->getPrimaryKey()] = $systemLogger;
        $this->loggers[$accessLogger->getPrimaryKey()] = $accessLogger;
    }

    /**
     * Returns the username configured in the system configuration.
     *
     * @return string The username
     */
    public function getUser()
    {
        $this->getParam('user');
    }

    /**
     * Returns the groupname configured in the system configuration.
     *
     * @return string The groupname
     */
    public function getGroup()
    {
        $this->getParam('group');
    }

    /**
     * Returns the umask configured in the system configuration.
     *
     * @return string The umask
     */
    public function getUmask()
    {
        $this->getParam('umask');
    }

    /**
     * Sets the passed base directory node.
     *
     * @param \AppserverIo\Appserver\Core\Api\Node\BaseDirectoryNode $baseDirectory The base directory node to set
     *
     * @return void
     */
    public function setBaseDirectory($baseDirectory)
    {
        $this->baseDirectory = $baseDirectory;
    }

    /**
     * Returns the node with the base directory information.
     *
     * @return \AppserverIo\Appserver\Core\Api\Node\BaseDirectoryNode The base directory information
     */
    public function getBaseDirectory()
    {
        return $this->baseDirectory;
    }

    /**
     * Returns the node containing information about the initial context.
     *
     * @return \AppserverIo\Appserver\Core\Api\Node\BaseDirectoryNode The initial context information
     */
    public function getInitialContext()
    {
        return $this->initialContext;
    }

    /**
     * Returns the array with all available loggers.
     *
     * @return array The available loggers
     */
    public function getLoggers()
    {
        return $this->loggers;
    }

    /**
     * Returns the array with registered extractors.
     *
     * @return array The registered extractors
     */
    public function getExtractors()
    {
        return $this->extractors;
    }

    /**
     * Returns the array with registered provisioners.
     *
     * @return array The registered provisioners
     */
    public function getProvisioners()
    {
        return $this->provisioners;
    }

    /**
     * Returns the array with all available containers.
     *
     * @return array The available containers
     */
    public function getContainers()
    {
        return $this->containers;
    }

    /**
     * Returns an array with the information about the deployed applications.
     *
     * @return array The array with the information about the deployed applications
     */
    public function getApps()
    {
        return $this->apps;
    }

    /**
     * Attaches the passed app node.
     *
     * @param \AppserverIo\Appserver\Core\Api\Node\AppNode $app The app node to attach
     *
     * @return void
     */
    public function attachApp(AppNode $app)
    {
        $this->apps[$app->getPrimaryKey()] = $app;
    }

    /**
     * Returns an array with the information about the deployed datasources.
     *
     * @return array The array with the information about the deployed datasources
     */
    public function getDatasources()
    {
        return $this->datasources;
    }

    /**
     * Attaches the passed datasource node.
     *
     * @param \AppserverIo\Appserver\Core\Api\Node\DatasourceNode $datasource The datasource node to attach
     *
     * @return void
     */
    public function attachDatasource(DatasourceNode $datasource)
    {
        $this->datasources[$datasource->getPrimaryKey()] = $datasource;
    }

    /**
     * Returns the array with all available scanners.
     *
     * @return array The available scanners
     */
    public function getScanners()
    {
        return $this->scanners;
    }
}
