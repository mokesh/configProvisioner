<?php

/**
 * This file is part of ConfigLoader package.
 *
 * (c) Mukesh Sharma <cogentmukesh@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mokesh;

use Igorw\Silex\ConfigDriver;
use Igorw\Silex\ChainConfigDriver;
use Igorw\Silex\PhpConfigDriver;
use Igorw\Silex\YamlConfigDriver;
use Igorw\Silex\JsonConfigDriver;
use Igorw\Silex\TomlConfigDriver;

/**
 * ConfigLoader Main Class
 *
 * @author Mukesh Sharma <cogentmukesh@gmail.com>
 * @since  Sun Oct 11 14:37:36 IST 2015
 * 
 * @package Mokesh
 */
class ConfigLoader implements ConfigLoaderInterface
{
    private $filename;
    private $replacements = array();
    private $driver;
    private $prefix = null;

    public function __construct($filename, array $replacements = array(), ConfigDriver $driver = null, $prefix = null)
    {
        $this->filename = $filename;
        $this->prefix = $prefix;

        if ($replacements) {
            foreach ($replacements as $key => $value) {
                $this->replacements['%'.$key.'%'] = $value;
            }
        }

        $this->driver = $driver ?: new ChainConfigDriver(array(
            new PhpConfigDriver(),
            new YamlConfigDriver(),
            new JsonConfigDriver(),
            new TomlConfigDriver(),
        ));
    }

    public function load(\ArrayAccess $array)
    {
        $config = $this->readConfig();

        foreach ($config as $name => $value)
            if ('%' === substr($name, 0, 1))
                $this->replacements[$name] = (string) $value;

        $this->merge($array, $config);
    }

    private function merge(\ArrayAccess $array, array $config)
    {
        if ($this->prefix) {
            $config = array($this->prefix => $config);
        }

        foreach ($config as $name => $value) {
            if (isset($array[$name]) && is_array($value)) {
                $array[$name] = $this->mergeRecursively($array[$name], $value);
            } else {
                $array[$name] = $this->doReplacements($value);
            }
        }
    }

    private function mergeRecursively(array $currentValue, array $newValue)
    {
        foreach ($newValue as $name => $value) {
            if (is_array($value) && isset($currentValue[$name])) {
                $currentValue[$name] = $this->mergeRecursively($currentValue[$name], $value);
            } else {
                $currentValue[$name] = $this->doReplacements($value);
            }
        }

        return $currentValue;
    }

    private function doReplacements($value)
    {
        if (!$this->replacements) {
            return $value;
        }

        if (is_array($value)) {
            foreach ($value as $k => $v) {
                $value[$k] = $this->doReplacements($v);
            }

            return $value;
        }

        if (is_string($value)) {
            return strtr($value, $this->replacements);
        }

        return $value;
    }

    private function readConfig()
    {
        if (!$this->filename) {
            throw new \RuntimeException('A valid configuration file must be passed before reading the config.');
        }

        if (!file_exists($this->filename)) {
            throw new \InvalidArgumentException(
                sprintf("The config file '%s' does not exist.", $this->filename));
        }

        if ($this->driver->supports($this->filename)) {
            return $this->driver->load($this->filename);
        }

        throw new \InvalidArgumentException(
                sprintf("The config file '%s' appears to have an invalid format.", $this->filename));
    }
}
