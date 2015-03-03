<?php

/**
 * \AppserverIo\Appserver\ServletEngine\Authentication\AuthenticationManager
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * PHP version 5
 *
 * @author    Florian Sydekum <fs@techdivision.com>
 * @copyright 2015 TechDivision GmbH <info@appserver.io>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/appserver-io/appserver
 * @link      http://www.appserver.io
 */

namespace AppserverIo\Appserver\ServletEngine\Authentication;

use AppserverIo\Psr\Servlet\ServletRequestInterface;
use AppserverIo\Psr\Servlet\ServletResponseInterface;
use AppserverIo\Psr\Application\ApplicationInterface;

/**
 * The authentication manager handles request which need Http authentication.
 *
 * @author    Florian Sydekum <fs@techdivision.com>
 * @copyright 2015 TechDivision GmbH <info@appserver.io>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/appserver-io/appserver
 * @link      http://www.appserver.io
 */
class StandardAuthenticationManager implements AuthenticationManagerInterface
{

    /**
     * Handles request in order to authenticate.
     *
     * @param \AppserverIo\Psr\Servlet\ServletRequestInterface  $servletRequest  The request instance
     * @param \AppserverIo\Psr\Servlet\ServletResponseInterface $servletResponse The response instance
     *
     * @return boolean TRUE if the authentication has been successful, else FALSE
     *
     * @throws \Exception
     */
    public function handleRequest(ServletRequestInterface $servletRequest, ServletResponseInterface $servletResponse)
    {

        // load the actual context instance
        $context = $servletRequest->getContext();

        // iterate over all servlets and return the matching one
        foreach ($context->search('ServletContextInterface')->getSecuredUrlConfigs() as $securedUrlConfig) {
            // continue if the can't find a config
            if ($securedUrlConfig == null) {
                continue;
            }

            // extract URL pattern and authentication configuration
            list ($urlPattern, $auth) = array_values($securedUrlConfig);

            // we'll match our URI against the URL pattern
            if (fnmatch($urlPattern, $servletRequest->getServletPath() . $servletRequest->getPathInfo())) {
                // load security configuration
                $configuredAuthType = $securedUrlConfig['auth']['auth-type'];

                // check the authentication type
                switch ($configuredAuthType) {
                    case "Basic":
                        $authImplementation =  'AppserverIo\Appserver\ServletEngine\Authentication\BasicAuthentication';
                        break;
                    case "Digest":
                        $authImplementation =  'AppserverIo\Appserver\ServletEngine\Authentication\DigestAuthentication';
                        break;
                    default:
                        throw new \Exception(sprintf('Unknown authentication type %s', $configuredAuthType));
                }

                // initialize the authentication manager
                $auth = new $authImplementation($securedUrlConfig);
                $auth->init($servletRequest, $servletResponse);

                // try to authenticate the request
                return $auth->authenticate();
            }
        }
    }

    /**
     * Initializes the manager instance.
     *
     * @return string
     * @see \AppserverIo\Psr\Application\ManagerInterface::initialize()
     */
    public function getIdentifier()
    {
        return AuthenticationManagerInterface::IDENTIFIER;
    }

    /**
     * Initializes the manager instance.
     *
     * @param \AppserverIo\Psr\Application\ApplicationInterface $application The application instance
     *
     * @return void
     * @see \AppserverIo\Psr\Application\ManagerInterface::initialize()
     */
    public function initialize(ApplicationInterface $application)
    {
    }

    /**
     * Returns the value with the passed name from the context.
     *
     * @param string $key The key of the value to return from the context.
     *
     * @return mixed The requested attribute
     */
    public function getAttribute($key)
    {
        throw new \Exception(sprintf('%s is not implemented yes', __METHOD__));
    }
}
