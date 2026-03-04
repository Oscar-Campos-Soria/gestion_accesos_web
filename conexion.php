<?php
// Configuración para XAMPP / MariaDB
$host = "localhost";
$dbname = "gestion_accesos";
$username = "root"; 
$password = "";     

try {
 
    $conexion = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    
   
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    

    
} catch(PDOException $e) {
   
    die("Error crítico al conectar con la base de datos: " . $e->getMessage());
}
?>