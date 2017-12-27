<?php

namespace yiiunit;

class InvalidateBehavior extends \mikk150\tagdependency\InvalidateBehavior
{
    public function exposedBuildKey($keyParts)
    {
        return parent::buildKey($keyParts);
    }
}
