<?php
// src/Controllers/TiendaController.php
require_once './Models/Tienda.php';

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class TiendaController
{
    public function alta(Request $request, Response $response, $args)
    {
        $params = (array)$request->getParsedBody();

        $marca = $params['marca'];
        $precio = $params['precio'];
        $tipo = $params['tipo'];
        $modelo = $params['modelo'];
        $color = $params['color'];
        $stock = $params['stock'];

        $tienda = new Tienda($marca, $precio, $tipo, $modelo, $color, $stock);
        $result = Tienda::save($tienda);

        $directory = __DIR__ . '/../../imagenesDeProductos/2024';
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        // Guardar la imagen si se ha proporcionado
        if ($request->getUploadedFiles()) {
            $uploadedFiles = $request->getUploadedFiles();
            $uploadedFile = $uploadedFiles['imagen'];
            if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
                $filename = sprintf('%s_%s.%s', $marca, $tipo, pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION));
                $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);
            }
        }

        $response->getBody()->write($result);
        return $response;
    }
    public function consultar(Request $request, Response $response, $args)
    {
        $params = (array)$request->getParsedBody();

        $marca = $params['marca'];
        $tipo = $params['tipo'];
        $color = $params['color'];

        $result = Tienda::findByMarcaTipoColor($marca, $tipo, $color);

        if ($result) {
            $response->getBody()->write("Existe");
        } else {
            $marcaExistente = Tienda::findByMarca($marca);
            $tipoExistente = Tienda::findByTipo($tipo);

            if (!$marcaExistente) {
                $response->getBody()->write("No hay productos de la marca $marca");
            } elseif (!$tipoExistente) {
                $response->getBody()->write("No hay productos del tipo $tipo");
            }
        }

        return $response;
    }
}
?>
