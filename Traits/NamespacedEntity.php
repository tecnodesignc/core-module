<?php

namespace Modules\Core\Traits;

trait NamespacedEntity
{

    public static function getEntityNamespace(): string
    {
        return isset(static::$entityNamespace) ? static::$entityNamespace : get_called_class();
    }

    public static function setEntityNamespace(string $namespace)
    {
        static::$entityNamespace = $namespace;
    }
}
