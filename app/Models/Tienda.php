<?php
require_once './DataBase/AccesoDatos.php';

class Tienda
{
    public $id;
    public $marca;
    public $precio;
    public $tipo;
    public $modelo;
    public $color;
    public $stock;

    public function __construct($marca, $precio, $tipo, $modelo, $color, $stock)
    {
        $this->marca = $marca;
        $this->precio = $precio;
        $this->tipo = $tipo;
        $this->modelo = $modelo;
        $this->color = $color;
        $this->stock = $stock;
    }

    public static function findByMarcaAndTipo($marca, $tipo)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM tienda WHERE marca = :marca AND tipo = :tipo");
        $consulta->bindValue(':marca', $marca, \PDO::PARAM_STR);
        $consulta->bindValue(':tipo', $tipo, \PDO::PARAM_STR);
        $consulta->execute();
        return $consulta->fetch(\PDO::FETCH_ASSOC);
    }

    public static function findByMarcaTipoColor($marca, $tipo, $color)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM tienda WHERE marca = :marca AND tipo = :tipo AND color = :color");
        $consulta->bindValue(':marca', $marca, \PDO::PARAM_STR);
        $consulta->bindValue(':tipo', $tipo, \PDO::PARAM_STR);
        $consulta->bindValue(':color', $color, \PDO::PARAM_STR);
        $consulta->execute();
        return $consulta->fetch(\PDO::FETCH_ASSOC);
    }
    public static function findByMarca($marca)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM tienda WHERE marca = :marca");
        $consulta->bindValue(':marca', $marca, \PDO::PARAM_STR);
        $consulta->execute();
        return $consulta->fetch(\PDO::FETCH_ASSOC);
    }

    public static function findByTipo($tipo)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM tienda WHERE tipo = :tipo");
        $consulta->bindValue(':tipo', $tipo, \PDO::PARAM_STR);
        $consulta->execute();
        return $consulta->fetch(\PDO::FETCH_ASSOC);
    }
    public static function findByMarcaTipoModelo($marca, $tipo, $modelo)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM tienda WHERE marca = :marca AND tipo = :tipo AND modelo = :modelo");
        $consulta->bindValue(':marca', $marca, \PDO::PARAM_STR);
        $consulta->bindValue(':tipo', $tipo, \PDO::PARAM_STR);
        $consulta->bindValue(':modelo', $modelo, \PDO::PARAM_STR);
        $consulta->execute();
        return $consulta->fetch(\PDO::FETCH_ASSOC);
    }

    public static function updateStock($id, $newStock)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("UPDATE tienda SET stock = :stock WHERE id = :id");
        $consulta->bindValue(':stock', $newStock, \PDO::PARAM_INT);
        $consulta->bindValue(':id', $id, \PDO::PARAM_INT);
        $consulta->execute();
    }
    public static function save($tienda)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();

        $existente = self::findByMarcaAndTipo($tienda->marca, $tienda->tipo);

        if ($existente) {
            $consulta = $objAccesoDatos->prepararConsulta("UPDATE tienda SET precio = :precio, stock = stock + :stock WHERE id = :id");
            $consulta->bindValue(':precio', $tienda->precio, \PDO::PARAM_STR);
            $consulta->bindValue(':stock', $tienda->stock, \PDO::PARAM_INT);
            $consulta->bindValue(':id', $existente['id'], \PDO::PARAM_INT);
            $consulta->execute();
            return "Alta tienda existente, stock y precio actualizado correctamente";
        } else {
            $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO tienda (marca, precio, tipo, modelo, color, stock) VALUES (:marca, :precio, :tipo, :modelo, :color, :stock)");
            $consulta->bindValue(':marca', $tienda->marca, \PDO::PARAM_STR);
            $consulta->bindValue(':precio', $tienda->precio, \PDO::PARAM_STR);
            $consulta->bindValue(':tipo', $tienda->tipo, \PDO::PARAM_STR);
            $consulta->bindValue(':modelo', $tienda->modelo, \PDO::PARAM_STR);
            $consulta->bindValue(':color', $tienda->color, \PDO::PARAM_STR);
            $consulta->bindValue(':stock', $tienda->stock, \PDO::PARAM_INT);
            $consulta->execute();
            return "Alta del producto correctamente";
        }
    }
}
?>
