<?php
require_once './Models/Venta.php'; // Asegúrate de que la ruta al archivo sea correcta

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class VentaController
{
    
    public function alta(Request $request, Response $response, $args)
    {
        $params = (array)$request->getParsedBody();

        $email = $params['email'];
        $marca = $params['marca'];
        $tipo = $params['tipo'];
        $modelo = $params['modelo'];
        $stock = intval($params['stock']);
        $precio = $params['precio'];

        $item = Tienda::findByMarcaTipoModelo($marca, $tipo, $modelo);

        if ($item && $item['stock'] >= $stock) {
            Tienda::updateStock($item['id'], $item['stock'] - $stock);

            $venta = new Venta($email, $marca, $tipo, $modelo, $stock,$precio);
            $result = Venta::save($venta);

            // Guardar la imagen si se ha proporcionado
            $directory = __DIR__ . '/../../imagenesDeVentas/2024';
            if (!is_dir($directory)) {
                mkdir($directory, 0777, true);
            }

            if ($request->getUploadedFiles()) {
                $uploadedFiles = $request->getUploadedFiles();
                $uploadedFile = $uploadedFiles['imagen'];
                if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
                    $filename = sprintf('%s_%s_%s_%s.jpg', $marca, $tipo, $modelo, $email);
                    $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);
                }
            }

            $response->getBody()->write($result);
        } else {
            if (!$item) {
                $response->getBody()->write("El ítem no se encontró en la tienda.");
            } else {
                $response->getBody()->write("No hay suficiente stock disponible.");
            }
        }

        return $response;
    }

    public function cantidadProductosVendidos(Request $request, Response $response, $args)
    {
        $params = $request->getQueryParams(); // Obtener parámetros de consulta
        $fecha = isset($params['fecha']) ? $params['fecha'] : date("Y-m-d", strtotime("-1 day")); // Obtener fecha, por defecto ayer si no se especifica

        try {
            // Consulta para obtener la cantidad de productos vendidos en la fecha indicada
            $cantidadProductos = Venta::cantidadProductosVendidos($fecha);

            // Devolver respuesta adecuada, por ejemplo, convertir a JSON y devolver en el cuerpo de la respuesta
            $response->getBody()->write(json_encode($cantidadProductos));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (PDOException $e) {
            // Manejo de errores
            $response->getBody()->write("Error al obtener la cantidad de productos vendidos: " . $e->getMessage());
            return $response->withStatus(500); // Internal Server Error
        }
    }

    // Función para obtener el listado de ventas de un usuario específico
    public function ventasPorUsuario(Request $request, Response $response, $args)
    {
        $params = $request->getQueryParams(); // Obtener parámetros de consulta
        $usuario = isset($params['usuario']) ? $params['usuario'] : '';

        try {
            // Lógica para obtener las ventas del usuario especificado
            $ventas = Venta::ventasPorUsuario($usuario);

            // Preparar la respuesta JSON
            $response->getBody()->write(json_encode($ventas));

            return $response->withHeader('Content-Type', 'application/json');
        } catch (PDOException $e) {
            // Manejo de errores si ocurre algún problema con la base de datos
            $response->getBody()->write(json_encode(['error' => 'Error al obtener las ventas del usuario']));
            return $response->withStatus(500); // Código de error HTTP 500 - Internal Server Error
        }
    }

    // Función para obtener el listado de ventas por tipo de producto
    public function ventasPorProducto(Request $request, Response $response, $args)
    {
        $params = $request->getQueryParams(); // Obtener parámetros de consulta
        $tipoProducto = isset($params['tipo']) ? $params['tipo'] : '';

        try {
            // Lógica para obtener las ventas por tipo de producto especificado
            $ventas = Venta::ventasPorProducto($tipoProducto);

            // Preparar la respuesta JSON
            $response->getBody()->write(json_encode($ventas));

            return $response->withHeader('Content-Type', 'application/json');
        } catch (PDOException $e) {
            // Manejo de errores si ocurre algún problema con la base de datos
            $response->getBody()->write(json_encode(['error' => 'Error al obtener las ventas por tipo de producto']));
            return $response->withStatus(500); // Código de error HTTP 500 - Internal Server Error
        }
    }

    // Función para obtener el listado de productos cuyo precio esté entre dos valores ingresados
    public function productosEntreValores(Request $request, Response $response, $args)
    {
        $params = $request->getQueryParams(); // Obtener parámetros de consulta
        $minPrecio = isset($params['min']) ? floatval($params['min']) : 0.0;
        $maxPrecio = isset($params['max']) ? floatval($params['max']) : PHP_FLOAT_MAX;

        try {
            // Lógica para obtener los productos cuyo precio esté entre los valores especificados
            $productos = Venta::productosEntreValores($minPrecio, $maxPrecio);

            // Preparar la respuesta JSON
            $response->getBody()->write(json_encode($productos));

            return $response->withHeader('Content-Type', 'application/json');
        } catch (PDOException $e) {
            // Manejo de errores si ocurre algún problema con la base de datos
            $response->getBody()->write(json_encode(['error' => 'Error al obtener los productos entre los valores de precio']));
            return $response->withStatus(500); // Código de error HTTP 500 - Internal Server Error
        }
    }

    // Función para obtener el listado de ingresos (ganancia de las ventas) por día de una fecha ingresada
    public function ingresosPorFecha(Request $request, Response $response, $args)
    {
        $params = $request->getQueryParams(); // Obtener parámetros de consulta
        $fecha = isset($params['fecha']) ? $params['fecha'] : '';

        try {
            // Lógica para obtener los ingresos por día de la fecha especificada
            $ingresos = Venta::ingresosPorFecha($fecha);

            // Preparar la respuesta JSON
            $response->getBody()->write(json_encode($ingresos));

            return $response->withHeader('Content-Type', 'application/json');
        } catch (PDOException $e) {
            // Manejo de errores si ocurre algún problema con la base de datos
            $response->getBody()->write(json_encode(['error' => 'Error al obtener los ingresos por fecha']));
            return $response->withStatus(500); // Código de error HTTP 500 - Internal Server Error
        }
    }

    // Función para obtener el producto más vendido
    public function productoMasVendido(Request $request, Response $response, $args)
    {
        try {
            // Lógica para obtener el producto más vendido
            $productoMasVendido = Venta::productoMasVendido();

            // Preparar la respuesta JSON
            $response->getBody()->write(json_encode($productoMasVendido));

            return $response->withHeader('Content-Type', 'application/json');
        } catch (PDOException $e) {
            // Manejo de errores si ocurre algún problema con la base de datos
            $response->getBody()->write(json_encode(['error' => 'Error al obtener el producto más vendido']));
            return $response->withStatus(500); // Código de error HTTP 500 - Internal Server Error
        }
    }

    public function modificarVenta(Request $request, Response $response, $args)
    {
        $params = (array) $request->getParsedBody();
    
        $id = isset($params['id']) ? $params['id'] : null;
        $email = isset($params['email']) ? $params['email'] : null;
        $marca = isset($params['marca']) ? $params['marca'] : null;
        $tipo = isset($params['tipo']) ? $params['tipo'] : null;
        $modelo = isset($params['modelo']) ? $params['modelo'] : null;
        $cantidad = isset($params['cantidad']) ? $params['cantidad'] : null;
    
        if ($id && $email && $marca && $tipo && $modelo && $cantidad) {
            // Utilizar buscarPorId para obtener la instancia de Venta
            $venta = Venta::buscarPorId($id);
    
            if ($venta) {
                // Actualizar los atributos del objeto Venta
                $venta->email = $email;
                $venta->marca = $marca;
                $venta->tipo = $tipo;
                $venta->modelo = $modelo;
                $venta->stock = $cantidad;
    
                // Llamar al método actualizar para guardar los cambios
                $result = $venta->actualizar();
    
                if ($result) {
                    $response->getBody()->write(json_encode(array('message' => 'Venta actualizada correctamente')));
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                } else {
                    $response->getBody()->write(json_encode(array('message' => 'Error al actualizar la venta')));
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
                }
            } else {
                $response->getBody()->write(json_encode(array('message' => 'Venta no encontrada')));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
            }
        } else {
            $response->getBody()->write(json_encode(array('message' => 'Faltan parámetros')));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
    }
    

}
    
?>
