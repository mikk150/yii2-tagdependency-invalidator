<?php

namespace yiiunit;


use yii\caching\FileCache;
use yii\caching\TagDependency;
use yiiunit\models\CacheFlushModel;

class CacheInvalidatorTest extends TestCase
{
    public function testTagDependencyInvalidate()
    {
        $cache = new FileCache(['cachePath' => '@yiiunit/runtime/cache']);
        
        $cache->set(['test', 1], 1, 0, new TagDependency(['tags' => [[0, 1]]]));
        $cache->set(['test', 2], 2, 0, new TagDependency(['tags' => [[0, 2]]]));

        $this->assertEquals(1, $cache->get(['test', 1]));
        $this->assertEquals(2, $cache->get(['test', 2]));
        
        TagDependency::invalidate($cache, [[0, 1]]);
        
        $this->assertFalse($cache->get(['test', 1]));
        $this->assertEquals(2, $cache->get(['test', 2]));
    }

    public function testBuildKey()
    {
        $model = new CacheFlushModel([
            'attribute' => 'test'
        ]);

        $behavior = new InvalidateBehavior([
            'tags' => [
                [
                    $model->className(),
                    'attribute' => 'attribute'
                ]
            ]
        ]);
        $model->attachBehavior('flushBehavior', $behavior);

        $keyArray = $behavior->exposedBuildKey([$model->className(), 'attribute' => 'attribute']);
        $this->assertContains($model->className(), $keyArray);
        $this->assertArrayHasKey('attribute', $keyArray);
        $this->assertEquals('test', $keyArray['attribute']);
    }

    public function testInvalidate()
    {
        $cache = new FileCache(['cachePath' => '@yiiunit/runtime/cache']);

        $model = new CacheFlushModel([
            'attribute' => 'test'
        ]);

        $behavior = new InvalidateBehavior([
            'cache' => $cache,
            'tags' => [
                [
                    $model->className(),
                    'attribute' => 'attribute'
                ]
            ]
        ]);
        $model->attachBehavior('flushBehavior', $behavior);

        $cache->set([__CLASS__, __METHOD__, 'cacheFlushModel', 'test'], $model, 0, new TagDependency([
            'tags' => [
                [$model->className(), 'attribute' => 'test']
            ]
        ]));

        $this->assertEquals($model, $cache->get([__CLASS__, __METHOD__, 'cacheFlushModel', 'test']));

        $model->invalidate();

        $this->assertFalse($cache->get([__class__, __METHOD__, 'cacheFlushModel', 'test']));
    }
}
