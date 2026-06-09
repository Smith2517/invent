<?php

namespace App\Core;

class Router
{
    private array $routes = [];
    private array $globalMiddlewares = [];

    /**
     * Registrar una ruta GET
     */
    public function get(string $path, string $handler, array $permissions = [], array $middlewares = []): self
    {
        $this->addRoute('GET', $path, $handler, $permissions, $middlewares);
        return $this;
    }

    /**
     * Registrar una ruta POST
     */
    public function post(string $path, string $handler, array $permissions = [], array $middlewares = []): self
    {
        // Por defecto, las peticiones POST agregan el Middleware CSRF para protección automática
        if (!in_array(\App\Middlewares\CsrfMiddleware::class, $middlewares)) {
            $middlewares[] = \App\Middlewares\CsrfMiddleware::class;
        }
        
        $this->addRoute('POST', $path, $handler, $permissions, $middlewares);
        return $this;
    }

    /**
     * Agregar una ruta a la colección interna
     */
    private function addRoute(string $method, string $path, string $handler, array $permissions, array $middlewares)
    {
        // Reemplazar placeholders tipo {id} o {slug} por grupos de expresión regular
        // {id} -> (\d+), otros -> ([^/]+)
        $pattern = preg_replace('/\{[a-zA-Z0-9_]+\}/', '([^/]+)', $path);
        $pattern = '#^' . $pattern . '$#';

        // Capturar los nombres de los parámetros para mapearlos después
        preg_match_all('/\{([a-zA-Z0-9_]+)\}/', $path, $matches);
        $paramNames = $matches[1];

        $this->routes[] = [
            'method'      => $method,
            'path'        => $path,
            'pattern'     => $pattern,
            'handler'     => $handler,
            'permissions' => $permissions,
            'middlewares' => $middlewares,
            'paramNames'  => $paramNames
        ];
    }

    /**
     * Despachar la petición y ejecutar el pipeline de la ruta coincidente
     */
    public function dispatch()
    {
        $request = new Request();
        $response = new Response();
        
        $uri = $request->getUri();
        $method = $request->getMethod();
        
        foreach ($this->routes as $route) {
            if ($route['method'] === $method && preg_match($route['pattern'], $uri, $matches)) {
                // Eliminar el primer elemento (toda la coincidencia) y dejar solo los grupos capturados
                array_shift($matches);
                
                // Mapear los valores de los parámetros capturados
                $params = [];
                foreach ($route['paramNames'] as $index => $name) {
                    $params[$name] = $matches[$index] ?? null;
                }

                // Crear pipeline de middlewares para esta ruta
                $middlewares = $route['middlewares'];
                
                // Si la ruta requiere algún permiso específico, inyectar el Middleware de Rol
                if (!empty($route['permissions'])) {
                    if (!in_array(\App\Middlewares\RoleMiddleware::class, $middlewares)) {
                        $middlewares[] = \App\Middlewares\RoleMiddleware::class;
                    }
                }

                // Resolver la ejecución secuencial de middlewares
                $this->runMiddlewarePipeline($middlewares, $route['permissions'], $request, $response, function() use ($route, $params, $request, $response) {
                    $this->executeHandler($route['handler'], $params, $request, $response);
                });
                
                return;
            }
        }

        // Si ninguna ruta coincide
        $response->setStatusCode(404);
        if ($request->getMethod() === 'POST' || strpos($uri, '/api/') !== false) {
            $response->error("La ruta solicitada no existe.", 404);
        } else {
            // Renderizar una vista de error amigable de 404
            $view = new View();
            $view->render('errors/404', ['title' => 'Página no encontrada'], 'auth');
        }
    }

    /**
     * Ejecuta secuencialmente los middlewares de la ruta
     */
    private function runMiddlewarePipeline(array $middlewares, array $permissions, Request $request, Response $response, callable $next)
    {
        if (empty($middlewares)) {
            $next();
            return;
        }

        $middlewareClass = array_shift($middlewares);
        
        if (class_exists($middlewareClass)) {
            $instance = new $middlewareClass();
            $instance->handle($request, $response, $permissions, function() use ($middlewares, $permissions, $request, $response, $next) {
                $this->runMiddlewarePipeline($middlewares, $permissions, $request, $response, $next);
            });
        } else {
            // Si el middleware no existe, continuar la cadena
            $this->runMiddlewarePipeline($middlewares, $permissions, $request, $response, $next);
        }
    }

    /**
     * Instanciar el controlador y ejecutar el método de acción
     */
    private function executeHandler(string $handler, array $params, Request $request, Response $response)
    {
        list($controllerName, $action) = explode('@', $handler);
        $fullControllerName = "\\App\\Controllers\\" . $controllerName;

        if (class_exists($fullControllerName)) {
            $controller = new $fullControllerName($request, $response);
            
            if (method_exists($controller, $action)) {
                // Pasar parámetros de ruta y de entrada al método de acción
                call_user_func_array([$controller, $action], [$params]);
            } else {
                die("Error: El método {$action} no existe en el controlador {$controllerName}.");
            }
        } else {
            die("Error: El controlador {$fullControllerName} no existe.");
        }
    }
}
