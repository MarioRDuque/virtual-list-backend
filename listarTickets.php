<?php

header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");


$json = file_get_contents('php://input');

$params = json_decode($json);

class Result
{
}

try {
    $complemento_fechas = " DATE(fecha_sacado) = '" . $params->fecha_sacado . "' order by fecha_sacado";
    include_once "utiles/base_de_datos.php";
    include_once "utiles/constantes.php";
    date_default_timezone_set($zonaHoraria);
    $query = "SELECT " . $params->tabla . ".* FROM " . $params->tabla . " 
    inner join fila fila on fila.codigo = codigo_fila 
    inner join sucursal su on su.codigo = fila.codigo_sucursal 
    where su.codigo = '" . $params->sucursal . "' and ";
    if ($params->tabla == 'ticket_programado') {
        $complemento_fechas = " DATE(fecha_cita) = '" . $params->fecha_sacado . "' order by hora_cita";
    }
    $query = $query . $complemento_fechas;
    $sentencia = $base_de_datos->query($query);
    $resultado = $sentencia->fetchAll(PDO::FETCH_OBJ);

    $response = new Result();
    $response->resultado = $resultado;
    $response->mensaje = 'Datos Listados Correctamente';

    header('Content-Type: application/json');
    echo json_encode($response);
} catch (Exception $th) {
    $response = new Result();
    $response->resultado = [];
    $response->mensaje = $th->getMessage();

    header('Content-Type: application/json');
    echo json_encode($response);
}
