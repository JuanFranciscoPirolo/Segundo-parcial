<?php
require_once './DataBase/AccesoDatos.php'; // Asegúrate de que la ruta al archivo sea correcta

class Venta
{
    public $id;
    public $email;
    public $marca;
    public $tipo;
    public $modelo;
    public $stock;
    public $fecha;
    public $numero_pedido;
    public $precio;

    public function __construct($email, $marca, $tipo, $modelo, $stock,$precio)
    {
        $this->email = $email;
        $this->marca = $marca;
        $this->tipo = $tipo;
        $this->modelo = $modelo;
        $this->stock = $stock;
        $this->fecha = date("Y-m-d");
        $this->numero_pedido = null; // Este campo será completado cuando se guarde en la BD
        $this->precio = $precio;
    }
    public function setId($id) {
        $this->id = $id;
    }

    public function setEmail($email) {
        $this->email = $email;
    }

    public function setMarca($marca) {
        $this->marca = $marca;
    }

    public function setTipo($tipo) {
        $this->tipo = $tipo;
    }

    public function setModelo($modelo) {
        $this->modelo = $modelo;
    }

    public function setStock($stock) {
        $this->stock = $stock;
    }

    public function setPrecio($precio) {
        $this->precio = $precio;
    }
    public static function cantidadProductosVendidos($fecha) {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        try {
            // Consulta para obtener la cantidad de productos vendidos en la fecha indicada
            $consulta = $objAccesoDatos->prepararConsulta("SELECT SUM(stock) AS cantidad_productos FROM ventas WHERE fecha = :fecha");
            $consulta->bindValue(':fecha', $fecha, PDO::PARAM_STR);
            $consulta->execute();

            return $consulta->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Manejo de errores
            echo "Error al obtener la cantidad de productos vendidos: " . $e->getMessage();
            return [];
        }
    }
    public static function save($venta)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();

        try {
            // Preparamos la consulta para insertar la venta
            $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO ventas (email, marca, tipo, modelo, stock, fecha, precio) VALUES (:email, :marca, :tipo, :modelo, :stock, :fecha, :precio)");
            $consulta->bindValue(':email', $venta->email, PDO::PARAM_STR);
            $consulta->bindValue(':marca', $venta->marca, PDO::PARAM_STR);
            $consulta->bindValue(':tipo', $venta->tipo, PDO::PARAM_STR);
            $consulta->bindValue(':modelo', $venta->modelo, PDO::PARAM_STR);
            $consulta->bindValue(':stock', $venta->stock, PDO::PARAM_INT);
            $consulta->bindValue(':fecha', $venta->fecha, PDO::PARAM_STR);
            $consulta->bindValue(':precio', $venta->precio, PDO::PARAM_STR);
            $consulta->execute();

            // Obtenemos el último ID insertado y lo asignamos al objeto venta
            $venta->id = $objAccesoDatos->obtenerUltimoId();

            return "Venta registrada correctamente con ID: " . $venta->id;
        } catch (PDOException $e) {
            return "Error al registrar la venta: " . $e->getMessage();
        }
    }
    
    public static function obtenerTodos() 
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        try {
            // Consulta para obtener todas las ventas
            $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM ventas");
            $consulta->execute();
            $ventas = $consulta->fetchAll(PDO::FETCH_CLASS, 'Venta');

            return $ventas;
        } catch (PDOException $e) {
            // Manejo de errores
            echo "Error al obtener las ventas: " . $e->getMessage();
            return [];
        }
    }

    // Método para obtener detalles de un pedido dado su ID
    public static function obtenerDetalles($id) {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        try {
            // Consulta para obtener los detalles de un pedido
            $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM detalle_pedido WHERE id = :id");
            $consulta->bindValue(':id', $id, PDO::PARAM_INT);
            $consulta->execute();

            return $consulta->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Manejo de errores
            echo "Error al obtener detalles del pedido: " . $e->getMessage();
            return [];
        }
    }

    // Método para obtener los productos cuyo precio esté entre dos valores
    public static function productosEntreValores($minPrecio, $maxPrecio) {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        try {
            // Consulta para obtener los productos cuyo precio esté entre los valores especificados
            $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM ventas WHERE precio BETWEEN :minPrecio AND :maxPrecio");
            $consulta->bindValue(':minPrecio', $minPrecio, PDO::PARAM_INT);
            $consulta->bindValue(':maxPrecio', $maxPrecio, PDO::PARAM_INT);
            $consulta->execute();

            return $consulta->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Manejo de errores
            echo "Error al obtener los productos entre los valores de precio: " . $e->getMessage();
            return [];
        }
    }

// Método para obtener los ingresos por fecha
public static function ingresosPorFecha($fecha) 
{
    $objAccesoDatos = AccesoDatos::obtenerInstancia();
    try {
        // Consulta para obtener los ingresos por día de la fecha especificada
        $consulta = $objAccesoDatos->prepararConsulta("SELECT SUM(stock * precio) AS ingresos FROM ventas WHERE fecha = :fecha");
        $consulta->bindValue(':fecha', $fecha, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Manejo de errores
        echo "Error al obtener los ingresos por fecha: " . $e->getMessage();
        return [];
    }
}


    // Método para obtener el producto más vendido
    public static function productoMasVendido() {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        try {
            // Consulta para obtener el producto más vendido
            $consulta = $objAccesoDatos->prepararConsulta("SELECT modelo, COUNT(*) AS total_ventas FROM ventas GROUP BY modelo ORDER BY total_ventas DESC LIMIT 1");
            $consulta->execute();

            return $consulta->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Manejo de errores
            echo "Error al obtener el producto más vendido: " . $e->getMessage();
            return [];
        }
    }

    // Método para obtener las ventas por tipo de producto
    public static function ventasPorProducto($tipo) {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        try {
            // Consulta para obtener las ventas por tipo de producto especificado
            $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM ventas WHERE tipo = :tipo");
            $consulta->bindValue(':tipo', $tipo, PDO::PARAM_STR);
            $consulta->execute();

            return $consulta->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Manejo de errores
            echo "Error al obtener las ventas por tipo de producto: " . $e->getMessage();
            return [];
        }
    }

    // Método para obtener las ventas de un usuario específico
    public static function ventasPorUsuario($usuario) {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        try {
            // Consulta para obtener las ventas del usuario especificado
            $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM ventas WHERE email = :usuario");
            $consulta->bindValue(':usuario', $usuario, PDO::PARAM_STR);
            $consulta->execute();

            return $consulta->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Manejo de errores
            echo "Error al obtener las ventas por usuario: " . $e->getMessage();
            return [];
        }
    }
// Método estático para buscar una venta por número de pedido
public static function buscarPorId($id) {
    $objAccesoDatos = AccesoDatos::obtenerInstancia();
    try {
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM ventas WHERE id = :id");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();

        // Fetch the result as an associative array
        $ventaData = $consulta->fetch(PDO::FETCH_ASSOC);

        if ($ventaData) {
            // Create a new instance of Venta and populate it with data
            $venta = new Venta(0,0,0,0,0,0);
            $venta->setId($ventaData['id']);  // Assuming setId, setEmail, setMarca, etc., methods exist in your Venta class
            $venta->setEmail($ventaData['email']);
            $venta->setMarca($ventaData['marca']);
            $venta->setTipo($ventaData['tipo']);
            $venta->setModelo($ventaData['modelo']);
            $venta->setStock($ventaData['stock']);
            $venta->setPrecio($ventaData['precio']);

            return $venta;
        } else {
            return null; // No venta found with the given ID
        }
    } catch (PDOException $e) {
        echo "Error al buscar la venta por número de pedido: " . $e->getMessage();
        return null;
    }
}

    

// Método para actualizar los datos de una venta
public function actualizar() {
    $objAccesoDatos = AccesoDatos::obtenerInstancia();

    try {
        // Preparamos la consulta para actualizar la venta
        $consulta = $objAccesoDatos->prepararConsulta("UPDATE ventas SET email = :email, marca = :marca, tipo = :tipo, modelo = :modelo, stock = :stock WHERE id = :id");
        $consulta->bindValue(':email', $this->email, PDO::PARAM_STR);
        $consulta->bindValue(':marca', $this->marca, PDO::PARAM_STR);
        $consulta->bindValue(':tipo', $this->tipo, PDO::PARAM_STR);
        $consulta->bindValue(':modelo', $this->modelo, PDO::PARAM_STR);
        $consulta->bindValue(':stock', $this->stock, PDO::PARAM_INT);
        $consulta->bindValue(':id', $this->id, PDO::PARAM_INT);
        $consulta->execute();

        return true; // Devuelve true si la actualización fue exitosa
    } catch (PDOException $e) {
        echo "Error al actualizar la venta: " . $e->getMessage();
        return false;
    }
}

}
?>
