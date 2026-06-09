<?php
/**
 * Definición de Rutas del Enrutador Dinámico
 * 
 * Parámetros del Router:
 * $router->get($ruta, $controladorAccion, $permisosRequeridos, $middlewaresExtra);
 * $router->post($ruta, $controladorAccion, $permisosRequeridos, $middlewaresExtra);
 */

// Middlewares comunes
$auth = [\App\Middlewares\AuthMiddleware::class];

// -----------------------------------------------------
// Rutas Públicas y de Autenticación
// -----------------------------------------------------
$router->get('/login', 'AuthController@login');
$router->post('/login', 'AuthController@loginPost');
$router->get('/logout', 'AuthController@logout', [], $auth);
$router->get('/forbidden', 'AuthController@forbidden');

// -----------------------------------------------------
// Dashboard Principal
// -----------------------------------------------------
$router->get('/', 'DashboardController@index', ['DASHBOARD_VIEW'], $auth);

// -----------------------------------------------------
// Rutas de Bienes Patrimoniales (Cambiado de /assets a /bienes para evitar conflicto de carpeta física)
// -----------------------------------------------------
$router->get('/bienes', 'AssetController@index', ['ASSET_VIEW'], $auth);
$router->post('/bienes/eliminar/{id}', 'AssetController@delete', ['ASSET_DELETE'], $auth);
$router->get('/bienes/ficha/{id}', 'AssetController@print', ['ASSET_PRINT'], $auth);
$router->get('/bienes/etiqueta/{id}', 'AssetController@label', ['ASSET_PRINT'], $auth);
$router->get('/bienes/exportar', 'AssetController@export', ['ASSET_EXPORT'], $auth);

// Endpoints AJAX para CRUD en Modales
$router->get('/api/bienes/detalle/{id}', 'AssetController@ajaxDetail', ['ASSET_VIEW'], $auth);
$router->post('/api/bienes/guardar', 'AssetController@ajaxSave', ['ASSET_CREATE'], $auth);
$router->post('/api/bienes/actualizar/{id}', 'AssetController@ajaxUpdate', ['ASSET_EDIT'], $auth);

// APIs AJAX Auxiliares para Bienes
$router->get('/api/subgroups/by-group/{group_id}', 'SubgroupController@byGroup', ['ASSET_VIEW'], $auth);

// -----------------------------------------------------
// Rutas de Gestión de Parámetros de Bienes (Grupos y Subgrupos)
// -----------------------------------------------------
// Rutas de Grupos
$router->get('/groups', 'GroupController@index', ['ROLE_VIEW'], $auth);
$router->get('/api/groups/detalle/{id}', 'GroupController@ajaxDetail', ['ROLE_VIEW'], $auth);
$router->post('/api/groups/guardar', 'GroupController@ajaxSave', ['ROLE_EDIT'], $auth);
$router->post('/api/groups/actualizar/{id}', 'GroupController@ajaxUpdate', ['ROLE_EDIT'], $auth);
$router->post('/api/groups/eliminar/{id}', 'GroupController@ajaxDelete', ['ROLE_EDIT'], $auth);

// Rutas de Subgrupos
$router->get('/api/subgroups/detalle/{id}', 'SubgroupController@ajaxDetail', ['ROLE_VIEW'], $auth);
$router->post('/api/subgroups/guardar', 'SubgroupController@ajaxSave', ['ROLE_EDIT'], $auth);
$router->post('/api/subgroups/actualizar/{id}', 'SubgroupController@ajaxUpdate', ['ROLE_EDIT'], $auth);
$router->post('/api/subgroups/eliminar/{id}', 'SubgroupController@ajaxDelete', ['ROLE_EDIT'], $auth);

// -----------------------------------------------------
// Rutas de Responsables de Custodio
// -----------------------------------------------------
$router->get('/responsibles', 'ResponsibleController@index', ['ROLE_VIEW'], $auth);
$router->get('/api/responsibles/detalle/{id}', 'ResponsibleController@ajaxDetail', ['ROLE_VIEW'], $auth);
$router->post('/api/responsibles/guardar', 'ResponsibleController@ajaxSave', ['ROLE_EDIT'], $auth);
$router->post('/api/responsibles/actualizar/{id}', 'ResponsibleController@ajaxUpdate', ['ROLE_EDIT'], $auth);
$router->post('/api/responsibles/eliminar/{id}', 'ResponsibleController@ajaxDelete', ['ROLE_EDIT'], $auth);

// -----------------------------------------------------
// Rutas de Oficinas / Áreas
// -----------------------------------------------------
$router->get('/offices', 'OfficeController@index', ['ROLE_VIEW'], $auth);
$router->get('/api/offices/detalle/{id}', 'OfficeController@ajaxDetail', ['ROLE_VIEW'], $auth);
$router->post('/api/offices/guardar', 'OfficeController@ajaxSave', ['ROLE_EDIT'], $auth);
$router->post('/api/offices/actualizar/{id}', 'OfficeController@ajaxUpdate', ['ROLE_EDIT'], $auth);
$router->post('/api/offices/eliminar/{id}', 'OfficeController@ajaxDelete', ['ROLE_EDIT'], $auth);

// -----------------------------------------------------
// Rutas de Fuentes de Financiamiento
// -----------------------------------------------------
$router->get('/funding-sources', 'FundingSourceController@index', ['ROLE_VIEW'], $auth);
$router->get('/api/funding-sources/detalle/{id}', 'FundingSourceController@ajaxDetail', ['ROLE_VIEW'], $auth);
$router->post('/api/funding-sources/guardar', 'FundingSourceController@ajaxSave', ['ROLE_EDIT'], $auth);
$router->post('/api/funding-sources/actualizar/{id}', 'FundingSourceController@ajaxUpdate', ['ROLE_EDIT'], $auth);
$router->post('/api/funding-sources/eliminar/{id}', 'FundingSourceController@ajaxDelete', ['ROLE_EDIT'], $auth);

// -----------------------------------------------------
// Rutas de Locales / Localidades
// -----------------------------------------------------
$router->get('/locations', 'LocationController@index', ['ROLE_VIEW'], $auth);
$router->get('/api/locations/detalle/{id}', 'LocationController@ajaxDetail', ['ROLE_VIEW'], $auth);
$router->post('/api/locations/guardar', 'LocationController@ajaxSave', ['ROLE_EDIT'], $auth);
$router->post('/api/locations/actualizar/{id}', 'LocationController@ajaxUpdate', ['ROLE_EDIT'], $auth);
$router->post('/api/locations/eliminar/{id}', 'LocationController@ajaxDelete', ['ROLE_EDIT'], $auth);


// -----------------------------------------------------
// Rutas de Inventario Físico Directo (DataTables, Modales y AJAX)
// -----------------------------------------------------
$router->get('/inventories', 'InventoryController@index', ['INVENTORY_VIEW'], $auth);
$router->get('/api/inventories/detail/{id}', 'InventoryController@jsonDetail', ['INVENTORY_VIEW'], $auth);
$router->post('/api/inventories/verify/{id}', 'InventoryController@ajaxSave', ['INVENTORY_EDIT'], $auth);
$router->post('/api/inventories/reset/{id}', 'InventoryController@ajaxReset', ['INVENTORY_EDIT'], $auth);

// -----------------------------------------------------
// Rutas de Usuarios y Roles (Modal/AJAX)
// -----------------------------------------------------
$router->get('/roles', 'RoleController@index', ['ROLE_VIEW'], $auth);
$router->get('/api/roles/detalle/{id}', 'RoleController@ajaxDetail', ['ROLE_VIEW'], $auth);
$router->post('/api/roles/actualizar/{id}', 'RoleController@ajaxUpdate', ['ROLE_EDIT'], $auth);

$router->get('/users', 'UserController@index', ['USER_VIEW'], $auth);
$router->get('/api/users/detalle/{id}', 'UserController@ajaxDetail', ['USER_VIEW'], $auth);
$router->post('/api/users/guardar', 'UserController@ajaxSave', ['USER_CREATE'], $auth);
$router->post('/api/users/actualizar/{id}', 'UserController@ajaxUpdate', ['USER_EDIT'], $auth);
$router->post('/api/users/eliminar/{id}', 'UserController@ajaxDelete', ['USER_DELETE'], $auth);
