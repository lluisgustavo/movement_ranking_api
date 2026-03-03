<?php

declare(strict_types=1);

namespace App\Http;

class Router
{
    private array $routes = [];

    // Para essas rotas, eu aceito qualquer coisa executável como handler.
    // Isso é para buscar o ranking de um movimento.
    public function get(string $pattern, callable $handler): void
    {
        $this->addRoute('GET', $pattern, $handler);
    }
 
    // POST é para criar um novo registro.
    public function post(string $pattern, callable $handler): void
    {
        $this->addRoute('POST', $pattern, $handler);
    }

    // PUT é para atualizar um registro existente.
    public function put(string $pattern, callable $handler): void
    {
        $this->addRoute('PUT', $pattern, $handler);
    }

    // PATCH é para atualizar parcialmente um registro existente.
    public function patch(string $pattern, callable $handler): void
    {
        $this->addRoute('PATCH', $pattern, $handler);
    }

    // DELETE é para deletar um registro existente.
    public function delete(string $pattern, callable $handler): void
    {
        $this->addRoute('DELETE', $pattern, $handler);
    }

    private function addRoute(string $method, string $pattern, callable $handler): void
    {
        $this->routes[] = [
            'method' => $method,
            'pattern' => $pattern,
            'handler' => $handler,
        ];
    }

    public function dispatch(string $method, string $uri): void
    {
        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }
 

            $pattern = "#^" . preg_replace('#\{([^}]+)\}#', '([^/]+)', $route['pattern']) . "$#";
 
            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches);
                call_user_func_array($route['handler'], $matches);
                return;
            }
        }

        JsonResponse::error('Rota não encontrada', 404)->send();
    }
}