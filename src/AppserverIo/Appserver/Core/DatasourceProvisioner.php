<?php

/**
 * AppserverIo\Appserver\Core\DatasourceProvisioner
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

namespace AppserverIo\Appserver\Core;

/**
 * Standard provisioning functionality.
 *
 * @category   Server
 * @package    Appserver
 * @subpackage Application
 * @author     Tim Wagner <tw@appserver.io>
 * @copyright  2014 TechDivision GmbH <info@appserver.io>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
 */
class DatasourceProvisioner extends AbstractProvisioner
{

    /**
     * Provision all datasources recursively found in the web application directory.
     *
     * @return void
     */
    public function provision()
    {

        // check if deploy dir exists
        if (is_dir($directory = $this->getWebappsDir())) {

            // load the datasource files
            $datasourceFiles = $this->getService()->globDir($directory . DIRECTORY_SEPARATOR . '*-ds.xml');

            // Iterate through all provisioning files (provision.xml) and attach them to the configuration
            foreach ($datasourceFiles as $datasourceFile) {

                // load the database configuration
                $datasourceNodes = $this->getService()->initFromFile($datasourceFile);

                // store the datasource in the system configuration
                foreach ($datasourceNodes as $datasourceNode) {
                    $this->getService()->attachDatasource($datasourceNode);
                }
            }
        }
    }
}
