<?php

namespace sersid\config\components;
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
     * @var string
     */
    public $modelClass = 'sersid\config\models\Config';

    /**
     *
     * @var array
     */
    public $modelOptions = [];

    /**
     *
     * @var \sersid\config\models\Config
     */
    public $model;

    /**
     * Config data
     * @var array
     */
    private $_data;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->coding = !empty($this->coding) ? $this->coding : self::CODING_JSON;
        parent::init();
    }

    /**
     * Get model
     * @return \sersid\config\models\Config
     */
    protected function getModel()
    {
        if ($this->model === null) {
            $this->model = new $this->modelClass($this->modelOptions);
        }
        return $this->model;
    }

    /**
     * Get data
     * @return array
     */
    public function getData()
    {
        if ($this->_data === null) {
            $this->_data = ArrayHelper::map($this->getModel()->find()->all(), 'key', 'value');
        }
        return $this->_data;
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
	 * Returns all parameters
	 * @return array
	 */
	public function getAll()
	{
		return $this->decode($this->getData());
	}

    /**
     * @inheritdoc
     */
    public function set($name, $value = null)
    {
        $model = $this->getModel();

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
                /**
                 * @todo moving to a model
                 */
                $model->getDb()->createCommand()
                        ->delete($model->tableName(), ['IN', 'key', $arDelete])
                        ->execute();
                
                $model->getDb()->createCommand()
                        ->batchInsert($model->tableName(), ['key', 'value'], $arInsert)
                        ->execute();
            }
        } else {
            $value = $this->_merge($name, $value);

            if (array_key_exists($name, $this->getData()) === false) {
                /**
                 * @todo moving to a model
                 */
                $model->getDb()->createCommand()->insert($model->tableName(), [
                    'key' => $name,
                    'value' => $value,
                ]);
            } else {
                /**
                 * @todo moving to a model
                 */
                $model->getDb()->createCommand()->update($model->tableName(), [
                    'value' => $value,
                ], 'key=:key', array(':key' => $name));
            }
            $this->_data[$name] = $value;
        }
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
            $model = $this->getModel();
            /**
             * @todo moving to a model
             */
			$model->getDb()->createCommand()
					->delete($model->tableName(), 'key=:key', [':key' => $name]);

			unset($this->_data[$name]);
		}
    }

    /**
     * @inheritdoc
     */
    public function deleteAll()
    {
        $model = $this->getModel();
        /**
         * @todo moving to a model
         */
        $model->getDb()->createCommand()->delete($model->tableName());
        $this->_data = [];
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