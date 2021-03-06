<?php

header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");


$json = file_get_contents('php://input');

$params = json_decode($json);

try {

  include_once "utiles/base_de_datos.php";

  $query = "SELECT * FROM totem WHERE codigo = '$params->codigo';";
  $sentencia = $base_de_datos->query($query);
  $resultado = $sentencia->fetchAll(PDO::FETCH_OBJ);

  $queryfilas = "SELECT * FROM totem_fila WHERE codigo_totem = '$params->codigo';";
  $sentenciafilas = $base_de_datos->query($queryfilas);

  $filas = [];
  if ($resultado && $resultado[0]) {
    $filas = $sentenciafilas->fetchAll(PDO::FETCH_OBJ);
  }

  class Result
  {
  }

  $response = new Result();
  $response->resultado = $resultado[0];
  $response->filas = $filas;
  $response->mensaje = 'Datos Listados Correctamente';

  header('Content-Type: application/json');
  echo json_encode($response);
} catch (Exception $th) {
  $response = new Result();
  $response->resultado = [];
  $response->mensaje = "error desconocido";
}
