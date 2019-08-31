<?php

namespace tests\unit;

use Codeception\Test\Unit;
use mikk150\tagdependency\InvalidateBehavior;
use yii\base\DynamicModel;
use yii\base\InvalidConfigException;
use yii\caching\ArrayCache;
use yii\caching\TagDependency;
use yii\db\ActiveRecord;

class InvalidateBehaviorTest extends Unit
{
    public function testCacheIsEnsured()
    {
        $this->expectException(InvalidConfigException::class);
        new InvalidateBehavior;
    }

    public function testCacheIsFlushedAfterDelete()
    {
        $cache = new ArrayCache;
        $cache->set('cache-key', 'cache-value', null, new TagDependency([
            'tags' => [
                'tag-name',
            ],
        ]));

        $model = new ActiveRecord;
        $model->attachBehavior('invalidate', [
            'class' => InvalidateBehavior::class,
            'cache' => $cache,
            'tags' => [
                'tag-name',
            ],
        ]);

        $model->trigger(ActiveRecord::EVENT_AFTER_DELETE);

        $this->assertFalse($cache->get('cache-key'));
    }

    public function testCacheIsFlushedAfterUpdate()
    {
        $cache = new ArrayCache;
        $cache->set('cache-key', 'cache-value', null, new TagDependency([
            'tags' => [
                ['tag-name'],
            ],
        ]));

        $model = new ActiveRecord;
        $model->attachBehavior('invalidate', [
            'class' => InvalidateBehavior::class,
            'cache' => $cache,
            'tags' => [
                ['tag-name'],
            ],
        ]);

        $model->trigger(ActiveRecord::EVENT_AFTER_UPDATE);

        $this->assertFalse($cache->get('cache-key'));
    }

    public function testCacheIsFlushedAfterInsert()
    {
        $cache = new ArrayCache;
        $cache->set('cache-key', 'cache-value', null, new TagDependency([
            'tags' => [
                [
                    DynamicModel::class,
                    'country' => 'Whatever',
                ],
            ],
        ]));
        $cache->set('cache-key-2', 'cache-value', null, new TagDependency([
            'tags' => [
                [
                    DynamicModel::class,
                    'country' => 'Unknown',
                ],
            ],
        ]));
        $cache->set('cache-key-3', 'cache-value', null, new TagDependency([
            'tags' => [
                [
                    DynamicModel::class,
                    'country' => 'Unknown',
                ],
                [
                    'with-integer',
                    123
                ]
            ],
        ]));

        $model = new DynamicModel([
            'country' => 'Whatever',
        ]);
        $model->attachBehavior('invalidate', [
            'class' => InvalidateBehavior::class,
            'cache' => $cache,
            'tags' => [
                [
                    DynamicModel::class,
                    'country' => 'country',
                ],
                [
                    'with-integer',
                    123
                ],
            ],
        ]);

        $model->trigger(ActiveRecord::EVENT_AFTER_INSERT);

        $this->assertFalse($cache->get('cache-key'));
        $this->assertNotFalse($cache->get('cache-key-2'));
        $this->assertFalse($cache->get('cache-key-3'));
    }
}
