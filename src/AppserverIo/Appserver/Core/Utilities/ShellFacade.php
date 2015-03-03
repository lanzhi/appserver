<?php

/**
 * \AppserverIo\Appserver\Core\Utilities\ShellFacade
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * PHP version 5
 *
 * @author    Bernhard Wick <bw@appserver.io>
 * @copyright 2015 TechDivision GmbH - <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/appserver-io/appserver
 * @link      http://www.techdivision.com/
 */

namespace AppserverIo\Appserver\Core\Utilities;

/**
 * Shell facade to wrap away the exec() shell interface so certain things like forbidden
 * commands can be implemented. It also helps mocking any results coming from the shell.
 *
 * @author    Bernhard Wick <bw@appserver.io>
 * @copyright 2015 TechDivision GmbH - <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/appserver-io/appserver
 * @link      http://www.techdivision.com/
 */
class ShellFacade
{

    /**
     * Commands which are prohibited to pass the facade.
     *
     * @var array
     */
    protected $prohibitedCommands;

    /**
     * Default constructor
     *
     * @param array $prohibitedCommands Initially prohibited commands
     */
    public function __construct($prohibitedCommands = array())
    {
        $this->prohibitedCommands = $prohibitedCommands;
    }

    /**
     * Will execute a shell command using exec() function.
     *
     * @param string $command The command to execute over the shell
     *
     * @throws \Exception
     *
     * @return mixed
     */
    public function exec($command)
    {
        // Check for prohibited commands
        foreach ($this->prohibitedCommands as $prohibitedCommand) {
            if (strpos(trim($command), $prohibitedCommand) !== false) {
                throw new \Exception('The shell command ' . $command . ' is prohibited');
            }
        }
        return exec($command);
    }

    /**
     * Getter for the list of prohibited commands
     *
     * @return array
     */
    public function getProhibitedCommands()
    {
        return $this->prohibitedCommands;
    }

    /**
     * Setter for the list of prohibited commands
     *
     * @param array $prohibitedCommands The list of prohibited commands
     *
     * @return void
     */
    public function setProhibitedCommands(array $prohibitedCommands)
    {
        $this->prohibitedCommands = $prohibitedCommands;
    }
}
