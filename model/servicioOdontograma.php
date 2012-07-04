<?php

class Model_ServicioOdontograma extends Model {

    private static $instance;

    public static function getInstance() {
        if (!isset(self::$instance)) {
            $a = __CLASS__;
            self::$instance = new $a;
        }
        return self::$instance;
    }

    public function __construct() {
        parent::__construct();
    }

    public function crearOdontograma($rows) {
        try {
            $r = $rows->fetch();
            $colPiezas = array();
            $colCaras = array();
            $colEstados = array();
            $colOdontogramas = array();
            $odontograma = new Model_Odontograma();
            $pieza = new Model_Pieza();
            $cara = new Model_Cara();
            $odontograma->setId($r['id_odontograma']);
            $odontograma->setFecha("hola");
            $pieza->setId($r['id_pieza']);
            $pieza->setNombre($r['nombre_pieza']);
            $pieza->setUrl_imagen($r['url_pieza']);
            $cara->setId($r['id_cara']);
            $cara->setDescripcion($r['desc_cara']);
            $estado = new Model_Estado();
            $estado->setId($r['id_estado']);
            $estado->setEstado($r['desc_estado']);
            $estado->setUrl_img($r['url_estado']);
            array_push($colEstados, $estado);
            while ($row = $rows->fetch()) {
                if (isset($row) & $row['id_odontograma'] != $odontograma->getId()) {
                    $odontograma->setPiezas($colPiezas);
                    array_push($colOdontogramas, $odontograma);
                    $colPiezas = array();
                    $odontograma = new Model_Odontograma();
                    $odontograma->setId($row['id_odontograma']);
                    $odontograma->setFecha("hola");
                }
                if (isset($row) & $row['id_pieza'] != $pieza->getId()) {
                    $pieza->setCaras($colCaras);
                    array_push($colPiezas, $pieza);
                    $pieza = new Model_Pieza();
                    $colCaras = array();
                    $pieza->setId($row['id_pieza']);
                    $pieza->setNombre($row['nombre_pieza']);
                    $pieza->setUrl_imagen($row['url_pieza']);
                }
                if (isset($row) & $row['id_cara'] != $cara->getId()) {
                    $cara->setEstados($colEstados);
                    array_push($colCaras, $cara);
                    $cara = new Model_Cara();
                    $colEstados = array();
                    $cara->setId($row['id_cara']);
                    $cara->setDescripcion($row['desc_cara']);
                }
                $estado = new Model_Estado();
                $estado->setId($row['id_estado']);
                $estado->setEstado($row['desc_estado']);
                $estado->setUrl_img($row['url_estado']);
                array_push($colEstados, $estado);
            }
            $cara->setEstados($colEstados);
            array_push($colCaras, $cara);
            $pieza->setCaras($colCaras);
            array_push($colPiezas, $pieza);
            $odontograma->setPiezas($colPiezas);
            array_push($colOdontogramas, $odontograma);
            return $colOdontogramas;
        } catch (Exception $exc) {
            throw $exc->getMessage();
        }
    }

    public function nuevoOdontograma($odontogramaInicial, $idTratamiento) {
        try {
            $sql = "INSERT INTO tbl_odontograma (id_tratamiento,fecha) values($idTratamiento," . date("d-m-Y") . ")";
            $statement = $this->db->prepare($sql);
            $id = mysql_insert_id();
            while ($row = $odontogramaInicial->fetch()) {
                $id_pieza = $row['id_pieza'];
                $id_cara = $row['id_cara'];
                $id_estado = $row['id_estado'];
                $sql = "INSERT INTO tbl_odontograma_estado(id_odontograma,id_pieza,id_cara,id_estado,fecha_ins,usr_ins,fecha_upd,usr_upd) values
                ($id,$id_pieza,$id_cara,$id_estado,'" . date("d-m-Y H:m:s") . "',1,'" . date("d-m-Y H:m:s") . "',1)";
                $statement = $this->db->prepare($sql);
            }
            $statement->execute();
            return true;
        } catch (Exception $exc) {
            throw $exc->getMessage();
        }
    }

    public function verifODontInicial($idTratamiento) {
        try {
            $sql = "select min(id) from tbl_odontograma where id_tratamiento =" . $idTratamiento;
            $statement = $this->db->prepare($sql);
            $statement->execute();
            $b = false;
            if (!empty($statement)) {
                $b = true;
            }
            return $b;
        } catch (Exception $exc) {
            throw $exc->getMessage();
        }
    }

    public function getOdontograma($idTratamiento, $tipo) {
        try {
            $sql = "SELECT o.id_odontograma, o.id_pieza,o.id_cara,o.id_estado,p.nombre as nombre_pieza, p.url_img as url_pieza,
        c.descripcion as desc_cara,e.estado as desc_estado,e.url_img as url_estado FROM 
        tbl_odontograma_estado as o inner join tbl_piezas as p inner join
        tbl_cara as c inner join tbl_estado as e on o.id_pieza = p.id and o.id_cara = c.id and o.id_estado = e.id
        where id_odontograma = (select " . $tipo . "(id) from tbl_odontograma where id_tratamiento = " . $idTratamiento . ")
        order by o.id_odontograma, o.id_pieza,o.id_cara,o.id_estado";
            $statement = $this->db->prepare($sql);
            $statement->execute();
            $odontograma = new Model_Odontograma();
            $colOdontogramas = array();
            if (isset($statement)) {
                $colOdontogramas = $this->crearOdontograma($statement);
            }
            $odontograma = $colOdontogramas[0];
            return $odontograma;
        } catch (Exception $exc) {
            throw $exc->getMessage();
        }
    }

    public function getOdontogramasTratamiento($idTratamiento, $desdeFecha, $hastaFecha) {
        try {
            $sql = "SELECT o.id_odontograma, o.id_pieza,o.id_cara,o.id_estado,p.nombre as nombre_pieza, p.url_img as url_pieza,
        c.descripcion as desc_cara,e.estado as desc_estado,e.url_img as url_estado FROM 
        tbl_odontograma_estado as o inner join tbl_piezas as p inner join
        tbl_cara as c inner join tbl_estado as e on o.id_pieza = p.id and o.id_cara = c.id and o.id_estado = e.id
        where id_odontograma = (select id from tbl_odontograma where id_tratamiento =" . $idTratamiento;
            if (isset($desdeFecha) & isset($hastaFecha)) {
                $sql .=" and fecha BETWEEN '$desdeFecha' AND '$hastaFecha'";
            }
            $sql .=") order by o.id_odontograma, o.id_pieza,o.id_cara,o.id_estado";
            echo $sql;
            $statement = $this->db->prepare($sql);
            $statement->execute();
            $colOdontogramas = array();
            if (isset($statement)) {
                $colOdontogramas = $this->crearOdontograma($statement);
            }
            return $colOdontogramas;
        } catch (Exception $exc) {
            throw $exc->getMessage();
        }
    }

}

?>
