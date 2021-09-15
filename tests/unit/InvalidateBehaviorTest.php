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

    public function testPartIsNotArray()
    {
        $cache = new ArrayCache();

        $cache->set('cache-key', 'cache-value', null, new TagDependency([
            'tags' => [
               'not_array'
            ],
        ]));

        $model = $this->construct(ActiveRecord::class, [], [
            'getPrimaryKey' => 10,
            'refresh' => true,
        ]);

        $model->attachBehavior('invalidate', [
            'class' => InvalidateBehavior::class,
            'cache' => $cache,
            'tags' => [
               'not_array'
            ],
        ]);

        $model->trigger(ActiveRecord::EVENT_AFTER_DELETE);

        $this->assertFalse($cache->get('cache-key'));
    }

    public function testPartIsNotMapping()
    {
        $cache = new ArrayCache();

        $cache->set('cache-key', 'cache-value', null, new TagDependency([
            'tags' => [
               ['not_array']
            ],
        ]));

        $model = $this->construct(ActiveRecord::class, [], [
            'getPrimaryKey' => 10,
            'refresh' => true,
        ]);

        $model->attachBehavior('invalidate', [
            'class' => InvalidateBehavior::class,
            'cache' => $cache,
            'tags' => [
               ['not_array']
            ],
        ]);

        $model->trigger(ActiveRecord::EVENT_AFTER_DELETE);

        $this->assertFalse($cache->get('cache-key'));
    }

    public function testCacheIsFlushedAfterDelete()
    {
        $cache = new ArrayCache();

        $cache->set('cache-key', 'cache-value', null, new TagDependency([
            'tags' => [
                [
                    'primaryKey' => 10,
                ],
            ],
        ]));

        $model = $this->construct(ActiveRecord::class, [], [
            'attributes' => function () {
                return ['id'];
            },
            'getPrimaryKey' => 10,
            'refresh' => true,
        ]);

        $model->id = 10;

        $model->attachBehavior('invalidate', [
            'class' => InvalidateBehavior::class,
            'cache' => $cache,
            'tags' => [
                [
                    'primaryKey' => 'id',
                ]
            ],
        ]);

        $model->trigger(ActiveRecord::EVENT_AFTER_DELETE);

        $this->assertFalse($cache->get('cache-key'));
    }

    public function testCacheIsFlushedAfterUpdate()
    {
        $cache = new ArrayCache();

        $cache->set('cache-key', 'cache-value', null, new TagDependency([
            'tags' => [
                [
                    'primaryKey' => 10,
                ],
            ],
        ]));

        $model = $this->construct(ActiveRecord::class, [], [
            'attributes' => function () {
                return ['id'];
            },
            'getPrimaryKey' => 10,
            'refresh' => true,
        ]);

        $model->id = 10;

        $model->attachBehavior('invalidate', [
            'class' => InvalidateBehavior::class,
            'cache' => $cache,
            'tags' => [
                [
                    'primaryKey' => 'id',
                ]
            ],
        ]);

        $model->trigger(ActiveRecord::EVENT_AFTER_UPDATE);

        $this->assertFalse($cache->get('cache-key'));
    }

    public function testCacheIsFlushedAfterInsert()
    {
        $cache = new ArrayCache();

        $cache->set('cache-key', 'cache-value', null, new TagDependency([
            'tags' => [
                [
                    'primaryKey' => 10,
                ],
            ],
        ]));

        $model = $this->construct(ActiveRecord::class, [], [
            'attributes' => function () {
                return ['id'];
            },
            'getPrimaryKey' => 10,
            'refresh' => true,
        ]);

        $model->id = 10;

        $model->attachBehavior('invalidate', [
            'class' => InvalidateBehavior::class,
            'cache' => $cache,
            'tags' => [
                [
                    'primaryKey' => 'id',
                ]
            ],
        ]);

        $model->trigger(ActiveRecord::EVENT_AFTER_INSERT);

        $this->assertFalse($cache->get('cache-key'));
    }
}
