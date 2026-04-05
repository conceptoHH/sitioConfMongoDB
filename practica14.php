<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $folio_ingresado = trim($_POST['folio'] ?? '');

    if ($folio_ingresado === '' || !isset($_SESSION['email'])) {
        die("Error: sesión caducada o datos incompletos.");
    }

    $email = $_SESSION['email'];
    $id_conferencia = (int)$_SESSION['id_conferencia'];

    // 1. Buscar Usuario por Email y Folio
    $usuario = $collUsuarios->findOne(['email' => $email, 'folio' => $folio_ingresado]);

    if (!$usuario) {
        die("El código ingresado no es válido.");
    }
    $id_usuario = $usuario['id_usuario'];

    // 2. Verificar si ya tiene ticket para esa conferencia
    $ticketExistente = $collTickets->countDocuments([
        'id_usuario' => $id_usuario,
        'id_conferencia' => $id_conferencia
    ]);

    if ($ticketExistente > 0) {
        die("Ya estás registrado en esta conferencia.");
    }

    // 3. Verificar Cupo
    // Buscamos la conferencia
    $conferencia = $collConferencias->findOne(['id_conferencia' => $id_conferencia]);
    // Contamos tickets actuales
    $ticketsVendidos = $collTickets->countDocuments(['id_conferencia' => $id_conferencia]);

    if (($conferencia['cupo'] - $ticketsVendidos) <= 0) {
        die("Lo sentimos, ya no hay cupo.");
    }

    // 4. Insertar Ticket
    // Generar ID Ticket simple
    $ultimoTicket = $collTickets->findOne([], ['sort' => ['id_ticket' => -1]]);
    $nuevoIdTicket = ($ultimoTicket) ? $ultimoTicket['id_ticket'] + 1 : 1;

    $collTickets->insertOne([
        'id_ticket' => $nuevoIdTicket,
        'fecha_registro' => date('Y-m-d'),
        'id_usuario' => $id_usuario,
        'id_conferencia' => $id_conferencia
    ]);

    echo "<h1>¡Registro Exitoso!</h1>";
    echo "<p>Te has inscrito correctamente a: " . htmlspecialchars($conferencia['titulo_conf']) . "</p>";
    echo "<a href='index.php'>Volver al inicio</a>";
}
?>