<?php
session_start();

// Si ya inició sesión, lo mandamos al dashboard
if(isset($_SESSION['id_usuario'])){
    header("Location: dashboard.php");
    exit;
}

require_once 'conexion.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = trim($_POST['correo']);
    $password = $_POST['password'];
    $ip = $_SERVER['REMOTE_ADDR'];

    try {
        // 1. Buscamos al usuario por su correo
        $stmt = $conexion->prepare("SELECT id_usuario, nombre_completo, password, id_tipo FROM usuario WHERE correo = :correo");
        $stmt->bindParam(':correo', $correo);
        $stmt->execute();
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // 2. LA MAGIA: Usamos password_verify() para comparar lo que escribió con el hash guardado
        if ($user && password_verify($password, $user['password'])) {
            
            // Éxito: Guardar en bitácora
            $stmtBitacora = $conexion->prepare("INSERT INTO bitacora (id_usuario, direccion_ip, exito) VALUES (:id_usuario, :ip, 1)");
            $stmtBitacora->execute([':id_usuario' => $user['id_usuario'], ':ip' => $ip]);

            // Crear variables de sesión
            $_SESSION['id_usuario'] = $user['id_usuario'];
            $_SESSION['nombre_completo'] = $user['nombre_completo'];
            $_SESSION['id_tipo'] = $user['id_tipo'];

            // Redirigir al dashboard
            header("Location: dashboard.php");
            exit;

        } else {
            // Fallo
            $error = "Correo o contraseña incorrectos.";
            
            $id_fallido = $user ? $user['id_usuario'] : NULL;
            $stmtBitacora = $conexion->prepare("INSERT INTO bitacora (id_usuario, direccion_ip, exito) VALUES (:id_usuario, :ip, 0)");
            $stmtBitacora->execute([':id_usuario' => $id_fallido, ':ip' => $ip]);
        }
    } catch(PDOException $e) {
        $error = "Error del sistema: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Gestión de Accesos</title>
    
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

    <div class="card card-custom p-4" style="width: 100%; max-width: 400px;">
        <div class="card-body">
            
            <div class="text-center mb-4">
                <i class="bi bi-shield-lock text-primary" style="font-size: 3rem;"></i>
                <h3 class="mt-2">Iniciar Sesión</h3>
            </div>

            <?php if(isset($error) && !empty($error)): ?>
                <div class="alert alert-danger text-center p-2 mb-3" role="alert">
                    <i class="bi bi-exclamation-triangle-fill"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form action="index.php" method="POST">
                
                <div class="mb-3">
                    <label for="correo" class="form-label">Correo Electrónico</label>
                    <div class="input-group">
                        <span class="input-group-text icon-input">
                            <i class="bi bi-envelope"></i>
                        </span>
                        <input type="email" class="form-control" id="correo" name="correo" placeholder="usuario@empresa.com" required>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label for="password" class="form-label">Contraseña</label>
                    <div class="input-group">
                        <span class="input-group-text icon-input">
                            <i class="bi bi-lock"></i>
                        </span>
                        <input type="password" class="form-control" id="password" name="password" placeholder="********" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Ingresar
                </button>
            </form>

            <div class="text-center mt-4">
                <p class="mb-0 text-muted">¿No tienes cuenta? <a href="registro.php" class="text-decoration-none fw-bold">Regístrate aquí</a></p>
            </div>
            
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
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