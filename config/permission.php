<?php

use Spatie\Permission\DefaultTeamResolver;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return [
    'models' => [
    'permission' => App\Models\Permission::class,
    'role' => App\Models\Role::class,
],

    'column_names' => [
    'role_pivot_key' => 'role_id',
    'permission_pivot_key' => 'permission_id',
    'model_morph_key' => 'model_id',
    'team_foreign_key' => 'team_id',
],

'tables' => [
    'roles' => 'roles',
    'permissions' => 'permissions',
    'model_has_permissions' => 'model_has_permissions',
    'model_has_roles' => 'model_has_roles',
    'role_has_permissions' => 'role_has_permissions',
],

    'register_permission_check_method' => true,
    'register_permission_relations_methods' => true,
    'teams' => false,
    'display_permission_in_exception' => false,
    'display_role_in_exception' => false,
    'enable_wildcard_permission' => false,

    'cache' => [
    'expiration_time' => \DateInterval::createFromDateString('0 seconds'), // ← DESATIVAR CACHE
    'key' => 'spatie.permission.cache',
    'store' => 'default',
],

    /*
 * By default all permissions will be cached for 24 hours unless a permission or
 * role is updated. Then the cache will be flushed automatically.
 */
'cache_expiration_time' => \DateInterval::createFromDateString('24 hours'),

/*
 * When using the "Super Permissions" feature only a single query is used
 * to get all permissions. This is faster but requires that the permission
 * names are unique.
 */
'permissions_separation' => false,

/*
 * When set to true, the required foreign keys for the relationships
 * between the models will be created automatically.
 */
'foreign_keys' => true,

/*
 * 🔑 CONFIGURAÇÃO IMPORTANTE: Usar UUIDs em vez de bigInteger
 */
'use_uuid' => true,
];
