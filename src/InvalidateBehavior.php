<?php

namespace mikk150\tagdependency;

use yii\base\Behavior;
use yii\base\InvalidConfigException;
use yii\caching\CacheInterface;
use yii\caching\TagDependency;
use yii\db\BaseActiveRecord;
use yii\di\Instance;
use yii\helpers\ArrayHelper;

class InvalidateBehavior extends Behavior
{
    /**
     * @var CacheInterface|array|string
     */
    public $cache = 'cache';

    public $tags;

    /**
     * @inheritDoc
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();
        $this->cache = Instance::ensure($this->cache, CacheInterface::class);
    }

    /**
     * Invalidates the cache
     */
    public function invalidate()
    {
        $owner = $this->owner;
        if ($this->owner instanceof BaseActiveRecord) {
            $owner = clone $this->owner;
            $owner->refresh();
        }
        TagDependency::invalidate($this->cache, array_map([$this, 'buildKey'], (array) $this->tags, array_map(function () use ($owner) {
            return $owner;
        },(array) $this->tags)));
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
     * @param array $keyParts The key parts
     *
     * @return array The key.
     */
    protected function buildKey($keyParts, $owner)
    {
        if (!is_array($keyParts)) {
            return $keyParts;
        }
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
