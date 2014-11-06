<?php

namespace sersid\config\components;

/**
 * @author Sersid <sersONEd@gmail.com>
 */
interface ConfigInterface
{
    /**
	 * Get config var
	 * @param string $name
	 * @param mixed $default
	 * @return mixed
	 */
    public function get($name, $default = null);

    /**
     * Returns all parameters
     * @return array
     */
    public function getAll();
    
    /**
	 * Set config vars
	 * @param string $name
	 * @param mixed $value
	 */
    public function set($name, $value = null);
    
    /**
	 * Delete parameter
	 * @param string $name
	 */
	public function delete($name);
    
    /**
	 * Remove all data
	 */
    public function deleteAll();
    
    /**
	 * Encoding variable with the specified coding method
	 * @param mixed $value
	 * @return mixed
	 * @throws CException
	 */
	public function encode($value);
    
    /**
	 * Decoding variable with the specified coding method
	 * @param mixed $value
	 * @return mixed
	 * @throws CException
	 */
	public function decode($value);
}