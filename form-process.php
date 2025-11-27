<?php
// Muestra errores en desarrollo (comenta en producción)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// --- CONFIG ---
// Usa un remitente de TU dominio (en Hostinger crea p.ej. no-reply@tudominio.com)
$fromDomainEmail = "info@acrehonduras.com";  // <-- CAMBIAR
$notifyTo        = "acrehonduras@gmail.com";  // <-- a dónde quieres recibir
$siteName        = "Acre Honduras";

// Asegura método POST
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
  http_response_code(405);
  echo "Method Not Allowed";
  exit;
}

// Helper
function field($k){ return isset($_POST[$k]) ? trim($_POST[$k]) : ''; }

// Sanitiza
$name    = filter_var(field('name'), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$email   = filter_var(field('email'), FILTER_SANITIZE_EMAIL);
$phone   = filter_var(field('phone'), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$message = filter_var(field('message'), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$subject = field('subject');
if ($subject === '') { $subject = 'Nuevo contacto desde el sitio'; }
$subject .= ' - Contact from site';

// Valida
$errors = [];
if ($name === '')                              $errors[] = "Name is required.";
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required.";
if ($phone === '')                              $errors[] = "Phone is required.";
if ($message === '')                            $errors[] = "Message is required.";

if ($errors) {
  http_response_code(422);
  echo implode(' ', $errors);
  exit;
}

// Cuerpo
$Body  = "Name: {$name}\n";
$Body .= "Email: {$email}\n";
$Body .= "Phone: {$phone}\n";
$Body .= "Message:\n{$message}\n";

// Encabezados: From de tu dominio + Reply-To del usuario
$headers  = "From: {$siteName} <{$fromDomainEmail}>\r\n";
$headers .= "Reply-To: {$name} <{$email}>\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

// Enviar
$success = @mail($notifyTo, $subject, $Body, $headers);

// Respuesta
if ($success) {
  echo "success";
} else {
  http_response_code(500);
  echo "Something went wrong :(";
}
