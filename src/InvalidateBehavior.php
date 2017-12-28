<?php

namespace mikk150\tagdependency;

use yii\base\Behavior;
use yii\helpers\ArrayHelper;
use yii\caching\TagDependency;
use yii\db\BaseActiveRecord;
use yii\di\Instance;

class InvalidateBehavior extends Behavior
{
    public $cache;

    public $tags;

    public function init()
    {
        if (!$this->cache) {
            $this->cache = 'cache';
        }

        $this->cache = Instance::ensure($this->cache, 'yii\caching\CacheInterface');
    }

    /**
     * Invalidates the cache
     */
    public function invalidate()
    {
        TagDependency::invalidate($this->cache, array_map([$this, 'buildKey'], (array) $this->tags));
    }

    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            BaseActiveRecord::EVENT_AFTER_UPDATE => 'invalidate',
            BaseActiveRecord::EVENT_AFTER_INSERT => 'invalidate',
            BaseActiveRecord::EVENT_AFTER_DELETE => 'invalidate',
        ];
    }

    /**
     * @param      array  $keyParts  The key parts
     *
     * @return     array  The key.
     */
    protected function buildKey($keyParts)
    {
        $owner = $this->owner;
        return $this->map($keyParts, function ($part, $key) use ($owner) {
            if (is_int($key)) {
                return $part;
            }
            return ArrayHelper::getValue($owner, $part);
        });
    }

    /**
     * Map array with callable and return array that preserve association.
     *
     * @param      array   $array     The array
     * @param      Callable  $callable  The callable
     *
     * @return     array
     */
    protected function map($array, $callable)
    {
        return array_combine(
            array_keys($array),
            array_map(
                $callable,
                array_values($array),
                array_keys($array)
            )
        );
    }
}
