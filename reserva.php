<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'usuarios_y_coches.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $dni = $_POST['dni'];
    $modelo = $_POST['modelo'];
    $fecha_inicio = $_POST['fecha_inicio'];
    $duracion = $_POST['duracion'];
    $errores_validacion = [];

    if (empty($nombre)) {
        $errores_validacion['nombre'] = "Debe introducir un nombre";
    }
    function usuarioExiste($nombre, $apellido, $dni, $usuarios) {
        foreach ($usuarios as $usuario) {
            if (
                $usuario['nombre'] === $nombre &&
                $usuario['apellido'] === $apellido &&
                $usuario['dni'] === $dni
            ) {
                return true;
            }
        }
        return false;
    }

 $fecha_inicio_reserva = strtotime($fecha_inicio); 
 $fecha_fin = strtotime("+$duracion day", $fecha_inicio_reserva); 
 $fecha_fin = date('Y-m-d', $fecha_fin); 
    $usuario_existe = usuarioExiste($nombre, $apellido, $dni, USUARIOS);
    function comprobarDisponibilidad($modelo, $fecha_inicio_nueva, $fecha_fin_nueva, $coches) {
        foreach ($coches as $coche) {
            if ($coche['modelo'] == $modelo) {
                if ($coche['disponible'] === true) {
                    return true; 
                } else {
                    $fecha_inicio_reserva = strtotime($coche['fecha_inicio']);
                    $fecha_fin_reserva = strtotime($coche['fecha_fin']);
                    $fecha_inicio_nueva = strtotime($fecha_inicio_nueva);
                    $fecha_fin_nueva = strtotime($fecha_fin_nueva);
                    if (
                        ($fecha_inicio_nueva >= $fecha_inicio_reserva && $fecha_inicio_nueva <= $fecha_fin_reserva) || // La nueva reserva empieza dentro del rango
                        ($fecha_fin_nueva >= $fecha_inicio_reserva && $fecha_fin_nueva <= $fecha_fin_reserva) || // La nueva reserva termina dentro del rango
                        ($fecha_inicio_nueva <= $fecha_inicio_reserva && $fecha_fin_nueva >= $fecha_fin_reserva) // La nueva reserva cubre completamente la existente
                    ) {
                        return false;
                    }
                }
            }
        }
        return true;
    }
    

   
    $coche_disponible = comprobarDisponibilidad($modelo, $fecha_inicio, $fecha_fin, $coches);

   
    $usuario_existe = usuarioExiste($nombre, $apellido, $dni, USUARIOS);
    
    if (empty($apellido)) {
        $errores_validacion['apellido'] = "Debe introducir un apellido";
    }

    $fecha_actual = date("Y-m-d");
    if ($fecha_inicio <= $fecha_actual) {
        $errores_validacion['fecha_inicio'] = "La fecha de inicio debe ser posterior a la fecha actual.";
    }

    if ($duracion < 1 || $duracion > 30) {
        $errores_validacion['duracion'] = "La duración debe estar entre 1 y 30 días.";
    }

    
    if (strlen($dni) >= 9) {
        $numero_dni = substr($dni, 0, 8); 
        $letra_ingresada = strtoupper(substr($dni, 8, 1)); 
    
      
        function letra_nif($dni) {
            return substr("TRWAGMYFPDXBNJZSQVHLCKE",strtr($dni,"XYZ","012")%23,1);
        }
    
        $letra_real = letra_nif($numero_dni);
    

        if ($letra_ingresada != $letra_real) {
            $errores_validacion['dni'] = "El DNI introducido no es válido.";
        }
    }
  
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title></title>
    <style>
        body { font-family: 'Lucida Sans', 'Lucida Sans Regular', 'Lucida Grande', 'Lucida Sans Unicode', Geneva, Verdana, sans-serif }
        .incorrecto { background-color: red; }
        .correcto { background-color: green; }
    </style>
</head>
<body>

<?php if (empty($errores_validacion) && $usuario_existe): ?>
    <h1>Datos de reserva correcta</h1>
    <p>Nombre: <?= $nombre ?></p>
    <p>Apellido: <?=$apellido?></p>
    <p>Coche reservado:</p>
    <?php if ($modelo === 'Lancia Stratos'): ?>
        <img src="lancia-stratos.jpg" alt="Lancia Stratos" width="400px" height="400px">
    <?php elseif ($modelo === 'Audi Quattro'): ?>
        <img src="audi-quattro.jpg" alt="Audi Quattro" width="400px" height="400px"/>
    <?php elseif ($modelo === 'Ford Escort RS1800'): ?>
        <img src="ford-escort1800.jpg" alt="Ford Escort RS1800" width="400px" height="400px" />
    <?php elseif ($modelo=== 'Subaru Impreza 555'): ?>
        <img src="subaru-impreza555.jpg" alt="Subaru Impreza 555" width="400px" height="400px" />
    <?php endif; ?>
<?php else: ?>
    <h1>Errores en la reserva</h1>
    <p>Por favor, corrige los siguientes errores.</p>
    <ul>  
        <?php if (isset($errores_validacion['nombre'])): ?>
            <li class="incorrecto">Nombre: <?= $errores_validacion['nombre'] ?></li>
        <?php else: ?>
            <li>Nombre: <span class="correcto"><?= $nombre ?></span></li>
        <?php endif; ?>

        <?php if (isset($errores_validacion['apellido'])): ?>
            <li class="incorrecto">Apellido: <?= $errores_validacion['apellido'] ?></li>
        <?php else: ?>
            <li>Apellido: <span class="correcto"><?=$apellido ?></span></li>
        <?php endif; ?>

        <?php if (isset($errores_validacion['dni'])): ?>
            <li class="incorrecto">DNI: <?= $errores_validacion['dni'] ?></li>
        <?php else: ?>
            <li>DNI: <span class="correcto"><?= $dni ?></span></li>
        <?php endif; ?>
        <?php if (!$usuario_existe): ?>
         <p style='color: red'>El usuario no está registrado en el sistema. No puede realizar la reserva.</p>
         <?php endif; ?>
        <?php if (isset($errores_validacion['fecha_inicio'])): ?>
            <li class="incorrecto">Fecha inicio: <?= $errores_validacion['fecha_inicio'] ?></li>
        <?php else: ?>
            <li>Fecha inicio: <span class="correcto"><?= $fecha_inicio ?></span></li>
        <?php endif; ?>
        <?php if (isset($errores_validacion['duracion'])): ?>
            <li class="incorrecto">Duración: <?= $errores_validacion['duracion'] ?></li>
        <?php elseif (!$coche_disponible): ?>
        <li class="incorrecto">El coche no está disponible en estas fechas.</li>
        <?php else: ?>
            <li>Duración: <span class="correcto"><?= $duracion ?> días</span></li>
        <?php endif; ?>
    </ul>
    <p>Por favor, rellena el <a href="index.html">formulario</a> de nuevo.</p>
    
<?php endif;
} ?>
        
</body>
</html>