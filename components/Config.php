<?php

namespace sersid\config\components;

/**
 * @author Sersid <sersONEd@gmail.com>
 */
class Config extends \yii\base\Component implements ConfigInterface
{
    /**
     * Encoding/Decoding variable with the specified coding method values
     */
    const CODING_JSON = 'json';
    const CODING_SERIALIZE = 'serialize';

    /**
	 * Method for coding and decoding
	 * serialize or json
	 * @var string default static::CODING_JSON
     * @see init()
	 */
	public $coding;
    
    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->coding = in_array($this->coding, [static::CODING_JSON, static::CODING_SERIALIZE]) ? $this->coding : static::CODING_JSON;
        parent::init();
    }
    
    /**
     * @inheritdoc
     */
    public function get($name, $default = null)
    {
        
    }
    
    /**
     * @inheritdoc
     */
    public function set($name, $value = null)
    {
        
    }
    
    /**
     * @inheritdoc
     */
    public function delete($name)
    {
        
    }
    
    /**
     * @inheritdoc
     */
    public function deleteAll()
    {
        
    }
    
    /**
     * @inheritdoc
     */
    public function encode($value)
    {
        
    }
    
    /**
     * @inheritdoc
     */
    public function decode($value)
    {
        
    }
}