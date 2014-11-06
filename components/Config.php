<?php

namespace sersid\config\components;
use Yii;
use yii\helpers\Json;
use yii\helpers\ArrayHelper;
use yii\base\Exception;

/**
 * Config component
 * @todo Add caching
 * @author Sersid <sersONEd@gmail.com>
 * @copyright Copyright &copy; www.sersid.ru 2013-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
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
     * The ID of the connection component
     * @var string
     */
    public $idConnection = 'db';

    /**
     * Config table name
     * @var string
     */
    public $tableName = '{{%config}}';

    /**
     * The ID of the cache component
     * @var string
     */
    public $idCache;

    /**
     * The key identifying the value to be cached
     * @var string
     */
    public $cacheKey = 'config.component';

    /**
     * Config data
     * @var array
     */
    private $_data;

    /**
     * Returns the database connection component.
     * @var \yii\db\Connection the database connection.
     */
    private $_db;

    /**
     * @var \yii\caching\Cache
     */
    private $_cache;

    /**
     * @inheritdoc
     */
    public function init()
    {
        // Get coding
        $this->coding = !empty($this->coding) ? $this->coding : self::CODING_JSON;

        // Get db component
        $this->_db = Yii::$app->get($this->idConnection);
        if(!$this->_db instanceof \yii\db\Connection) {
            throw new Exception("Config.idConnection \"{$this->idConnection}\" is invalid.");
        }

        // Get cache component
        if($this->idCache !== NULL) {
            $this->_cache = Yii::$app->get($this->idCache);
            if(!$this->_cache instanceof \yii\caching\Cache) {
                throw new CException("Config.idCache \"{$this->idCache}\" is invalid.");
            }
        }

        parent::init();
    }

    /**
     * Get data
     * @return array
     */
    public function getData()
    {
        if ($this->_data === null) {
            if($this->_cache !== null) {
                $cache = $this->_cache->get($this->cacheKey);
                if($cache === false) {
                    $this->_data = $this->_getDataFromDb();
                    $this->_cache->set($this->cacheKey, $this->_data);
                } else {
                    $this->_data = $cache;
                }
            } else {
                $this->_data = $this->_getDataFromDb();
            }
        }
        return $this->_data;
    }

    /**
     * Get data from database
     * @return array
     */
    private function _getDataFromDb()
    {
        return ArrayHelper::map($this->_db->createCommand("SELECT * FROM {$this->tableName}")->queryAll(), 'key', 'value');
    }

    /**
     * Set cache
     */
    private function _setCache()
    {
        if($this->_cache !== null) {
            $this->_cache->set($this->cacheKey, $this->_data);
        }
    }

    /**
     * @example Yii::$app->config->param1;
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        return array_key_exists($name, $this->getData()) ? $this->_get($name) : parent::__get($name);
    }

    /**
     * @example Yii::$app->config->param1 = "value";
     * @param string $name
     * @param mixed $value
     * @return mixed
     */
    public function __set($name, $value)
    {
        return array_key_exists($name, $this->getData()) ? $this->set($name) : parent::__set($name, $value);
    }

    /**
     * @example isset(Yii::$app->config->param1);
     * @param string $name
     * @return boolean
     */
    public function __isset($name)
    {
        return array_key_exists($name, $this->getData()) ? true : parent::__isset($name);
    }

    /**
     * @example unset(Yii::$app->config->param1);
     * @param string $name
     * @return self::delete();
     */
    public function __unset($name)
    {
        return array_key_exists($name, $this->getData()) ? $this->delete($name) : parent::__unset($name);
    }

    /**
     * Get config var
     * @param string/array $name
     * @param mixed $default
     * @return mixed
     */
    public function get($name, $default = null)
    {
        if (is_array($name)) {
            $return = [];
            foreach ($name as $key => $value) {
                if (is_int($key)) {
                    $return[$value] = $this->_get($value, $default);
                } else {
                    $return[$key] = $this->_get($key, $value);
                }
            }
            return $return;
        }
        return $this->_get($name, $default);
    }

    /**
     * Find and decode config var
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    private function _get($name, $default = null)
    {
        $data = $this->getData();
        return array_key_exists($name, $data) ? $this->decode($data[$name]) : $default;
    }

    /**
     * @inheritdoc
     */
	public function getAll()
	{
        $return = [];
        foreach ($this->getData() as $key => $data) {
            $return[$key] = $this->get($key);
        }

        return $return;
	}

    /**
     * @inheritdoc
     */
    public function set($name, $value = null)
    {
        if (is_array($name)) {
            $arInsert = [];
            $arDelete = [];
            foreach ($name as $key => $val) {
                $val = $this->_merge($key, $val);
                $arInsert[] = [$key, $val];
                $arDelete[] = $key;
                $this->_data[$key] = $val;
            }
            if (count($arInsert) > 0) {
                $this->_db->createCommand()
                        ->delete($this->tableName, ['IN', 'key', $arDelete])
                        ->execute();

                $this->_db->createCommand()
                        ->batchInsert($this->tableName, ['key', 'value'], $arInsert)
                        ->execute();
            }
        } else {
            $value = $this->_merge($name, $value);

            if (array_key_exists($name, $this->getData()) === false) {
                $this->_db->createCommand()->insert($this->tableName, [
                    'key' => $name,
                    'value' => $value,
                ]);
            } else {
                $this->_db->createCommand()->update($this->tableName, [
                    'value' => $value,
                ], 'key=:key', array(':key' => $name));
            }
            $this->_data[$name] = $value;
        }

        $this->_setCache();
    }

    /**
	 * Merge parameters
	 * @param string $name
	 * @param mixed $value
	 * @return mixed
	 */
	private function _merge($name, $value, $encode = true)
	{
		if(is_array($value)) {
			$config = $this->_get($name);
			if(is_array($config)) {
                $value = ArrayHelper::merge($config, $value);
            }
		}

		if($encode === true) {
            $value = $this->encode($value);
        }

		return $value;
	}

    /**
     * @inheritdoc
     */
    public function delete($name)
    {
        if(array_key_exists($name, $this->getData())) {
            $this->_db->createCommand()
					->delete($this->tableName, 'key=:key', [':key' => $name]);

			unset($this->_data[$name]);
		}

        $this->_setCache();
    }

    /**
     * @inheritdoc
     */
    public function deleteAll()
    {
        $this->_db->createCommand()->delete($this->tableName);
        $this->_data = [];

        $this->_cache->delete($this->cacheKey);
    }

    /**
     * Encoding variable with the specified coding method
     * @param mixed $value
     * @return mixed
     * @throws Exception
     */
    public function encode($value)
    {
        switch ($this->coding) {
            case self::CODING_JSON:
                return Json::encode($value);
                break;

            case self::CODING_SERIALIZE:
                return serialize($value);
                break;

            default:
                throw new Exception("Config.coding \"{$this->coding}\" is invalid.");
                break;
        }
    }

    /**
     * Decoding variable with the specified coding method
     * @param mixed $value
     * @return mixed
     * @throws Exception
     */
    public function decode($value)
    {
        switch ($this->coding) {
            case self::CODING_JSON:
                return Json::decode($value);
                break;

            case self::CODING_SERIALIZE:
                return unserialize($value);
                break;

            default:
                throw new Exception("Config.coding \"{$this->coding}\" is invalid.");
                break;
        }
    }
}