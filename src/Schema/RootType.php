<?php

namespace Nuwave\Lighthouse\Schema;

use Exception;

class RootType
{
    public const QUERY = 'Query';
    public const MUTATION = 'Mutation';
    public const SUBSCRIPTION = 'Subscription';

    public static function getName(string $typeName): string
    {
        switch ($typeName) {
            case static::QUERY:
                return config('lighthouse.namespaces.queries');
            case static::MUTATION:
                return config('lighthouse.namespaces.mutations');
            case static::SUBSCRIPTION:
                return config('lighthouse.namespaces.subscriptions');
            default:
                throw new Exception('RootType of ' . $typeName . ' was not found');
        }
    }

    public static function isRootType(string $typeName): bool
    {
        return in_array(
            $typeName,
            [
                static::QUERY,
                static::MUTATION,
                static::SUBSCRIPTION,
            ]
        );
    }

    /**
     * @return array<int, string>
     */
    public static function defaultNamespaces(string $typeName): array
    {
        switch ($typeName) {
            case static::QUERY:
                return (array) config('lighthouse.namespaces.queries');
            case static::MUTATION:
                return (array) config('lighthouse.namespaces.mutations');
            case static::SUBSCRIPTION:
                return (array) config('lighthouse.namespaces.subscriptions');
            default:
                return [];
        }
    }
}
