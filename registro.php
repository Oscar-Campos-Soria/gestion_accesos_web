<?php
// Iniciar sesión y conectar a la base de datos
session_start();
if(isset($_SESSION['id_usuario'])){
    header("Location: dashboard.php");
    exit;
}

require_once 'conexion.php';

$mensaje = "";

// 1. Obtener los "Tipos de Usuario" dinámicamente de la base de datos
$tipos_usuario = [];
try {
    $stmtTipos = $conexion->query("SELECT id_tipo, nombre_tipo FROM tipo_usuario");
    $tipos_usuario = $stmtTipos->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $mensaje = "<div class='alert alert-danger'>Error al cargar roles: " . $e->getMessage() . "</div>";
}

// 2. Procesar el formulario cuando el usuario le da a "Registrarse"
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = trim($_POST['nombre']);
    $correo = trim($_POST['correo']);
    $password = $_POST['password']; 
    $id_tipo = $_POST['id_tipo'];

    try {
        // Verificar que el correo no esté repetido
        $stmtCheck = $conexion->prepare("SELECT id_usuario FROM usuario WHERE correo = :correo");
        $stmtCheck->execute([':correo' => $correo]);
        
        if ($stmtCheck->rowCount() > 0) {
            $mensaje = "<div class='alert alert-warning'><i class='bi bi-exclamation-triangle-fill me-2'></i>Ese correo ya está registrado.</div>";
        } else {
            // --- NUEVO: Encriptar la contraseña antes de guardarla ---
            $password_encriptada = password_hash($password, PASSWORD_DEFAULT);

            // Guardar al nuevo usuario en MariaDB usando la contraseña encriptada
            $stmt = $conexion->prepare("INSERT INTO usuario (nombre_completo, correo, password, id_tipo) VALUES (:nombre, :correo, :password, :id_tipo)");
            $stmt->execute([
                ':nombre' => $nombre,
                ':correo' => $correo,
                ':password' => $password_encriptada,
                ':id_tipo' => $id_tipo
            ]);
            $mensaje = "<div class='alert alert-success'><i class='bi bi-check-circle-fill me-2'></i>¡Registro exitoso! <a href='index.php' class='alert-link'>Inicia sesión aquí</a></div>";
        }
    } catch(PDOException $e) {
        $mensaje = "<div class='alert alert-danger'>Error del sistema: " . $e->getMessage() . "</div>";
    }
}
?>


<!DOCTYPE html>
<html lang="es" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Gestión de Accesos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="d-flex align-items-center justify-content-center vh-100 position-relative">

    <div class="position-absolute top-0 end-0 p-3">
        <button class="btn btn-outline-secondary rounded-circle" id="btn-theme" title="Cambiar Tema">
            <i class="bi bi-moon-stars-fill" id="icon-theme"></i>
        </button>
    </div>

    <div class="card card-custom p-4" style="width: 100%; max-width: 600px;">
        <div class="card-body">
            <div class="text-center mb-4">
                <i class="bi bi-person-plus text-success" style="font-size: 3rem;"></i>
                <h3 class="mt-2">Crear Cuenta</h3>
            </div>

            <?php echo $mensaje; ?>

            <form action="registro.php" method="POST" class="needs-validation" novalidate>
                
                <div class="row g-3 mb-3">
                    <div class="col-md-6 col-12">
                        <label for="nombre" class="form-label">Nombre Completo</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" required>
                        <div class="invalid-feedback">Por favor ingresa tu nombre.</div>
                    </div>
                    
                    <div class="col-md-6 col-12">
                        <label for="correo" class="form-label">Correo Electrónico</label>
                        <input type="email" class="form-control" id="correo" name="correo" required>
                        <div class="invalid-feedback">Ingresa un correo válido.</div>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6 col-12">
                        <label for="password" class="form-label">Contraseña</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                        <div class="invalid-feedback">La contraseña es obligatoria.</div>
                    </div>

                    <div class="col-md-6 col-12">
                        <label for="id_tipo" class="form-label">Tipo de Usuario</label>
                        <select class="form-select" id="id_tipo" name="id_tipo" required>
                            <option value="" selected disabled>Selecciona un rol...</option>
                            <?php foreach($tipos_usuario as $tipo): ?>
                                <option value="<?php echo $tipo['id_tipo']; ?>">
                                    <?php echo htmlspecialchars($tipo['nombre_tipo']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">Selecciona un tipo de usuario.</div>
                    </div>
                </div>

                <button type="submit" class="btn btn-success w-100 py-2">
                    <i class="bi bi-person-check me-2"></i>Registrarse
                </button>
            </form>

            <div class="text-center mt-4">
                <p class="mb-0 text-muted">¿Ya tienes cuenta? <a href="index.php" class="text-decoration-none fw-bold">Inicia sesión</a></p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        
        (() => {
            'use strict'
            const forms = document.querySelectorAll('.needs-validation')
            Array.from(forms).forEach(form => {
                form.addEventListener('submit', event => {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    form.classList.add('was-validated')
                }, false)
            })
        })()

        
        document.addEventListener('DOMContentLoaded', () => {
            const htmlElement = document.documentElement;
            const btnTheme = document.getElementById('btn-theme');
            const iconTheme = document.getElementById('icon-theme');
            const temaGuardado = localStorage.getItem('temaPreferido') || 'light';
            
            htmlElement.setAttribute('data-bs-theme', temaGuardado);
            actualizarIcono(temaGuardado);

            btnTheme.addEventListener('click', () => {
                const temaActual = htmlElement.getAttribute('data-bs-theme');
                const nuevoTema = temaActual === 'light' ? 'dark' : 'light';
                htmlElement.setAttribute('data-bs-theme', nuevoTema);
                localStorage.setItem('temaPreferido', nuevoTema);
                actualizarIcono(nuevoTema);
            });

            function actualizarIcono(tema) {
                if (tema === 'dark') {
                    iconTheme.classList.replace('bi-moon-stars-fill', 'bi-sun-fill');
                    btnTheme.classList.replace('btn-outline-secondary', 'btn-outline-light');
                } else {
                    iconTheme.classList.replace('bi-sun-fill', 'bi-moon-stars-fill');
                    btnTheme.classList.replace('btn-outline-light', 'btn-outline-secondary');
                }
            }
        });
    </script>
</body>
</html>