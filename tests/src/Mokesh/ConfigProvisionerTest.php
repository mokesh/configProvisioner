<?php

/*
 * This file is part of ConfigProvisioner Package.
 *
 * (c) Mukesh Sharma <cogentmukesh@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mokesh;

/**
 * @author Mukesh Sharma <cogentmukesh@gmail.com>
 */
class ConfigProvisionerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    private $expectedConfiguration = array (
        '__PREFIX__'  => array (
            'general' => array (
                'debug'         => 'true',
                'connections'   => 1000,
            ),
            'log' => array (
                'filename' => 'my-test.log',
                'level' => array ('INFO', 'DEBUG'),
            ),
            'API' => array (
                'key'    => 'my-personalize-key',
                'secret' => 'my-highly-secure-secret',
            ),
        ),
    );

    /**
     * Test Provision
     *
     * TODO: Need to improve and write more UnitTests
     * This is a very badly written and bare minimum test 
     */
    public function testProvision()
    {
        $configProvisioner = new ConfigProvisioner();
        $configProvisioner->load(new ConfigLoader(__DIR__."/fixtures/config.json", array('ENV' => 'test'), null, '__PREFIX__'));
        $configProvisioner->load(new ConfigLoader(__DIR__."/fixtures/config.php", array('ENV' => 'test'), null, '__PREFIX__'));

        $this->assertEquals($configProvisioner->provision(), $this->expectedConfiguration);
    }
}
