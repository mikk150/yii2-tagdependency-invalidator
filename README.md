# Yii2 tagdependency invalidation behavior

[![Build Status](https://travis-ci.org/mikk150/yii2-tagdependency-invalidator.svg?branch=master)](https://travis-ci.org/mikk150/yii2-tagdependency-invalidator)
[![codecov](https://codecov.io/gh/mikk150/yii2-tagdependency-invalidator/branch/master/graph/badge.svg)](https://codecov.io/gh/mikk150/yii2-tagdependency-invalidator)


Usage
-----
To use this behavior, add it to model's or components behaviors model

```php
class Book extends yii\base\ActiveRecord
{
    const CACHE_KEY = 'BOOKS_ARE_AWESOME!';

    public function behaviors()
    {
        return [
            [
                'class' => 'mikk150\tagdependency\InvalidateBehavior',
                'tags' => [
                    [
                        self::CACHE_KEY,
                        'id' => 'primaryKey',
                    ],
                ]
            ]
        ]
    }
}
```

then where you want to use cached models, just do this

```php

public function actionView($id)
{
    return Yii::$app->cache->getOrSet(['book', $id], function () use ($id) {
        return Book::find()->byId($id)->one();
    }, null, new TagDependency([
        'tags' => [
            [
                Book::CACHE_KEY,
                'id' => $id,
            ]
        ]
    ]))
}

```

if `BaseActiveRecord::EVENT_AFTER_UPDATE`, `BaseActiveRecord::EVENT_AFTER_INSERT` or `BaseActiveRecord::EVENT_AFTER_DELETE` is triggered, then cache is automatically invalidated for this model based on key rules

additionally, you can also clear cache by executing `invalidate()` on model1