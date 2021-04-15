<?php

header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");


$json = file_get_contents('php://input');

$params = json_decode($json);

$PNG_TEMP_DIR = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR;

//html PNG location prefix
$PNG_WEB_DIR = 'temp/';

class Result
{
}

try {

  include_once "utiles/base_de_datos.php";
  include_once "utiles/phpqrcode.php";
  // echo '<img src="' . $PNG_WEB_DIR . basename($filename) . '" />';
  date_default_timezone_set('America/Lima');
  $fecha = date("Y-m-d H:i:s");
  $recordar = $params->recordatorio == false ? 'no' : 'yes';
  $query =
    "INSERT INTO ticket_programado(codigo_tipo_operacion, email, telefono, recordatorio, fecha_sacado, fecha_cita, hora_cita, rut, nombres)
  VALUES ('$params->codigo_tipo_operacion', '$params->email', '$params->telefono', '$recordar', '$fecha', '$params->fecha_cita', '$params->hora_cita', '$params->rut', '$params->nombres') RETURNING hora_cita;
  ";

  $conexion = pg_connect("host=" . $rutaServidor . " port=" . $puerto . " dbname=" . $nombreBaseDeDatos . " user=" . $usuario . " password=" . $clave . "") or die('Error al conectar con la base de datos: ' . pg_last_error());
  $resource = pg_Exec($conexion, $query);
  $resultado = pg_fetch_object($resource);

  $response = new Result();

  if ($resultado) {
    $response->mensaje = 'Usted reservó un ticket para ser atendido. Revise su correo electrónico.';

    if (!file_exists($PNG_TEMP_DIR))
      mkdir($PNG_TEMP_DIR);

    $textoCodigo = $params->codigo_tipo_operacion . "-" . $params->fecha_cita . "-" . $params->hora_cita;
    $textoQR = $textoCodigo . " --> " . $fecha;

    $filename = $PNG_TEMP_DIR . 'test.png';
    $filename = $PNG_TEMP_DIR . 'test' . md5($textoQR . '|H|10') . '.png';
    QRcode::png($textoQR, $filename, 'H', '10', 2);
    $imagen = "";

    if ($filename) {
      $imgbinary = fread(fopen($filename, "r"), filesize($filename));
      $imagen = 'data:image/png;base64,' . base64_encode($imgbinary);
    }

    $subject = "Ticket Generado " . $textoCodigo;

    // $message = '<h3>Hola: ' . $params->nombres . ' <br> </h3> Usted reservó el siguiente ticket: 
    //           <img src="' . $imagen . '" />
    //           <img src="https://drive.google.com/file/d/1EjngHyBSjDtrd0SubkFlcE2FTlYSSICz/view?usp=sharing">          
    //           <br> 
    //           <img src="' . $imagen . '" />
    //           <img src="https://k60.kn3.net/E/7/F/2/4/8/472.gif">
    //           <br> 
    //           <img src="' . $imagen . '" />
    //           <img src="https://drive.google.com/file/d/1j4YJqotD7-VfAEbqXTW4t7QyF6f8v_Dx/view">
    //           Presentar el siguiente ticket al ingresar.';

    $message = '<html lang="es" style="font-family: sans-serif; font-size: 12px; font-weight: bold;">
              <head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
              </head>
              <style>
                *,
                *::before,
                *::after {
                  box-sizing: border-box;
                }
              </style>
              
              <body style="margin: 0; line-height: 1.5; text-align: left; margin-left: 10%; margin-right: 10%;">
                <div style="height: 10px; background-color: #c81d89;"></div>
                <div style="height: 4px; background-color: #365963;"></div>
                <div style="height: 100px;">
                </div>
                <div style="text-align: center; font-weight: bold; font-size: 18px; color: #365963;">
                  TICKET GENERADO VIRTUALMENTE </div>
                <div style="height: 4px; background-color: #365963;"></div>
                <div style="padding: 3% 3% 0 3%;">
                  <b style="font-size: 16px;"> Estimado (a) ' . $params->nombres . '</b>
                </div>
                <div style="padding: 3%;">
                  Es un placer saludarte, esperamos que todo est&eacute; saliendo bien, te informamos que se ha generado el siguiente
                  ticket electr&oacute;nico:
                </div>
                <div style="background-color: #dedede; padding-top: 5px; padding-bottom: 5px;">
                  <div
                    style="border-bottom-style: dashed; border-color: #fff; border-top-style: dashed; border-top-width: 2px; border-bottom-width: 2px;">
                    <div style="padding: 30px 3% 0 3%; display: flex;">
                      <div
                        style="color: white; text-align: right; padding: 2% 2% 0 2%; width: 33.333333%; max-width: 33.333333%; background-color: #365b61; border-radius: 20px 0 0 0;">
                        <div>Fila</div>
                      </div>
                      <div
                        style="padding: 2% 2% 0 2%; width: 66.666667%; max-width: 66.666667%; font-weight: bold; background-color: #ffff; border-radius: 0 5px 0 0;">
                        <div>' . $params->fila . '</div>
                      </div>
                    </div>
                    <div style="padding: 0 3% 0 3%; display: flex;">
                      <div
                        style="color: white; text-align: right; padding: 0 2% 0 2%; width: 33.333333%; max-width: 33.333333%; background-color: #365b61;">
                        <div>N&uacute;mero de Ticket</div>
                      </div>
                      <div
                        style="padding: 0 2% 0 2%; width: 66.666667%; max-width: 66.666667%; font-weight: bold; background-color: #ffff;">
                        <div>' . $params->codigo_tipo_operacion . "-" . $params->hora_cita . '</div>
                      </div>
                    </div>
                    <div style="padding: 0 3% 0 3%; display: flex;">
                      <div
                        style="color: white; text-align: right; padding: 0 2% 0 2%; width: 33.333333%; max-width: 33.333333%; background-color: #365b61;">
                        <div>Fecha de cita</div>
                      </div>
                      <div
                        style="padding: 0 2% 0 2%; width: 66.666667%; max-width: 66.666667%; font-weight: bold; background-color: #ffff;">
                        <div>' . $params->fecha_cita . "-" . $params->hora_cita . '</div>
                      </div>
                    </div>
                    <div style="padding: 0 3% 0 3%; display: flex;">
                      <div
                        style="color: white; text-align: right; padding: 0 2% 2% 2%; width: 33.333333%; max-width: 33.333333%; background-color: #365b61; border-radius: 0 0 0 20px;">
                        <div>Sucursal</div>
                      </div>
                      <div
                        style="padding: 0 2% 2% 2%; width: 66.666667%; max-width: 66.666667%; font-weight: bold; background-color: #ffff; border-radius: 0 0 5px 0;">
                        <div>' . $params->sucursal . '</div>
                      </div>
                    </div>
                    <div style="padding: 10px 3% 10px 3%; display: flex;">
                      <div style="color: white; text-align: right; padding: 2%; width: 33.333333%; max-width: 33.333333%;">
                      
                        <img src="https://k60.kn3.net/E/7/F/2/4/8/472.gif">
          
                      </div>
                      <div style="padding: 2%; width: 66.666667%; max-width: 66.666667%; color: #616060;">
                        <i>La representaci&oacute;n impresa del comprobante electr&oacute;nico es el archivo PDF adjunto, no posee validez
                          tributaria y es necesario que la imprima.</i>
                      </div>
                    </div>
                  </div>
                </div>
                <div style="text-align: center;">
                  <br>
                  <p>Gracias por preferirnos: <br></p>
                  <p>Atentamente,</p><br>
                  <div><strong>Empresa SA</strong></div>
                  <div><strong>RUC: </strong>0991011897001</div>
                  <div><strong>Direcci&oacute;n: </strong>VELEZ 423 Y ESCOBEDO</div>
                  <div><strong>Tel&eacute;fono: </strong></div>
                  <div><strong>Email: </strong>notificaciones@documentos-electronicos.info</div>
                  <br>
                  <br>
                </div>
                <div
                  style="text-align: center; color: #365b61;">
                  <br>
                  <br><br>
                  <div>QVirtual. Software electr&oacute;nico para la emisi&oacute;n de tickets, cont&aacute;ctenos al </div>
                  <div> (593)72966371 / (07)2924090 / 0939309203,</div>
                  <div>estaremos gustosos en atender sus requerimientos.</div>
                  <br><br>
                </div>
          
                <div style="height: 4px; background-color: #365963;"></div>
                <div style="height: 10px; background-color: #c81d89;"></div>
              </body>
              
              </html>';

    $cabeceras  = 'MIME-Version: 1.0' . "\r\n";
    $cabeceras .= 'Content-type:  text/html; charset=iso-8859-1' . "\r\n";

    $enviado = mail($params->email, $subject, $message, $cabeceras);
  } else {
    $response->mensaje = 'Ocurrió un error al reservar un ticket.';
  }
  $response->resultado = $resultado;

  header('Content-Type: application/json');
  echo json_encode($response);
} catch (Exception $th) {
  $response = new Result();
  $response->mensaje = $th->getMessage();

  header('Content-Type: application/json');
  echo json_encode($response);
}
