<?php
// Error Handling
error_reporting(-1);
ini_set('display_errors', 1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response as ResponseMw;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
//require_once './middlewares/Auth.php';

use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;
use Slim\Routing\RouteContext;

use Dotenv\Dotenv;


require_once './Controllers/TiendaController.php';
require_once './Controllers/VentaController.php';

require_once '../vendor/autoload.php';
//require_once './DataBase/AccesoDatos.php';

// Load ENV
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();


$app = AppFactory::create();
$app->addRoutingMiddleware();
$errorMiddleware = $app->addErrorMiddleware(true, true, true);
$app->addBodyParsingMiddleware();



$app->group('/tienda', function (RouteCollectorProxy $group) {
    $group->post('/alta', TiendaController::class . ':alta');
    $group->post('/consultar', TiendaController::class . ':consultar');

});

$app->group('/ventas', function (RouteCollectorProxy $group) {
    $group->post('/alta', VentaController::class . ':alta');
        // Endpoint para consultar cantidad de productos vendidos en un día específico
        $group->get('/productos/vendidos', VentaController::class . ':cantidadProductosVendidos');

        // Endpoint para listar ventas de un usuario específico
        $group->get('/porUsuario', VentaController::class . ':ventasPorUsuario');
    
        // Endpoint para listar ventas por tipo de producto
        $group->get('/porProducto', VentaController::class . ':ventasPorProducto');
    
        // Endpoint para listar productos cuyo precio esté entre dos valores ingresados
        $group->get('/productos/entreValores', VentaController::class . ':productosEntreValores');
    
        // Endpoint para listar ingresos por día de una fecha ingresada
        $group->get('/ingresos', VentaController::class . ':ingresosPorFecha');
    
        // Endpoint para mostrar el producto más vendido
        $group->get('/productos/masVendido', VentaController::class . ':productoMasVendido');
        $group->put('/modificar', VentaController::class . ':modificarVenta');
    
});

$app->run();
?>