<?php

namespace App\Controllers\Api;

use CodeIgniter\Controller;
use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'Gyminix API',
    description: 'API REST para el sistema de gestión de gimnasios Gyminix',
)]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'JWT',
    description: 'Token JWT obtenido en POST /auth/login',
)]
#[OA\Server(
    url: 'http://localhost/Gyminix/backend/public/api/v1',
    description: 'Entorno local (XAMPP)',
)]
class SwaggerController extends Controller
{
    public function ui()
    {
        if (ENVIRONMENT !== 'development') {
            return $this->response->setStatusCode(404)->setBody('Not Found');
        }

        $jsonUrl = base_url('api/v1/docs/json');

        $html = <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gyminix API — Docs</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://unpkg.com/swagger-ui-dist@5/swagger-ui.css">
    <style>body { margin: 0; }</style>
</head>
<body>
<div id="swagger-ui"></div>
<script src="https://unpkg.com/swagger-ui-dist@5/swagger-ui-bundle.js"></script>
<script>
SwaggerUIBundle({
    url: "{$jsonUrl}",
    dom_id: '#swagger-ui',
    presets: [SwaggerUIBundle.presets.apis, SwaggerUIBundle.SwaggerUIStandalonePreset],
    layout: 'BaseLayout',
    deepLinking: true,
    persistAuthorization: true,
    tryItOutEnabled: true,
});
</script>
</body>
</html>
HTML;

        return $this->response->setBody($html)->setContentType('text/html');
    }

    public function json()
    {
        if (ENVIRONMENT !== 'development') {
            return $this->response->setStatusCode(404)->setBody('{}');
        }

        $openapi = (new \OpenApi\Generator())->generate([APPPATH . 'Controllers/Api']);

        if ($openapi === null) {
            return $this->response->setStatusCode(500)->setBody('{"error":"No se pudo generar el spec OpenAPI"}');
        }

        return $this->response
            ->setHeader('Content-Type', 'application/json')
            ->setHeader('Access-Control-Allow-Origin', '*')
            ->setBody($openapi->toJson());
    }
}
