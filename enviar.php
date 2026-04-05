<?php
session_start();
require 'db.php';
require 'vendor/autoload.php'; // PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre         = trim($_POST['nombre'] ?? '');
    $email          = trim($_POST['email'] ?? '');
    $id_conferencia = (int)($_POST['id_conferencia'] ?? 0);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $nombre === '' || $id_conferencia === 0) {
        die("Datos inválidos.");
    }

    // --- LÓGICA MONGODB: Obtener ID incremental ---
    // En Mongo no hay AUTO_INCREMENT nativo fácil. 
    // Generaremos un ID aleatorio o usaremos el count + 1 (simple pero riesgo de colisión en concurrencia alta)
    // Para este ejercicio usaremos un ID aleatorio único para el usuario o dejaremos que Mongo use _id
    // Pero como tu lógica usa 'id_usuario' numérico, haremos esto:
    $ultimoUsuario = $collUsuarios->findOne([], ['sort' => ['id_usuario' => -1]]);
    $nuevoIdUsuario = ($ultimoUsuario) ? $ultimoUsuario['id_usuario'] + 1 : 1;

    $folio = mt_rand(100000, 999999);

    // Insertar Usuario
    $resultado = $collUsuarios->insertOne([
        'id_usuario' => $nuevoIdUsuario,
        'nombre' => $nombre,
        'email' => $email,
        'folio' => (string)$folio // Guardar como string
    ]);

    if ($resultado->getInsertedCount() != 1) {
        die("Error al guardar usuario en Mongo.");
    }

    // Enviar correo (Misma lógica que tenías, simplificada aquí)
    $mail = new PHPMailer(true);
    try {

        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'molinapasst@gmail.com';
        $mail->Password   = 'CONTRASEÑA_DE_APLICACION'; 
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        $mail->setFrom('molinapasst@gmail.com', 'REGISTRO CONFERENCIA');
        $mail->addAddress($email, $nombre);

        $mail->isHTML(true);
        $mail->Subject = 'Tu codigo de verificacion';
        $mail->Body    = "Hola $nombre,<br><br>Tu código de verificación es: <b>$folio</b>";

        $mail->send();
        
        // Guardar en sesión
        $_SESSION['email']          = $email;
        $_SESSION['nombre']         = $nombre;
        $_SESSION['id_conferencia'] = $id_conferencia;

        echo "Usuario registrado. Folio: <b>$folio</b><br>";
        echo "Simulación de correo enviado.<br>";
        
        // Formulario para validar
        echo '<form action="practica14.php" method="POST">
                <label>Ingresa tu código:</label>
                <input type="text" name="folio" required>
                <button type="submit">Confirmar</button>
              </form>';

    } catch (Exception $e) {
        echo "Error al enviar correo: {$mail->ErrorInfo}";
    }
}
?>
