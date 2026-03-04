<?php
session_start();
if(!isset($_SESSION['id_usuario'])){
    header("Location: index.php");
    exit;
}

require_once 'conexion.php';

try {
    $sql = "SELECT b.id_registro, u.nombre_completo, b.fecha_acceso, b.direccion_ip, b.exito 
            FROM bitacora b 
            LEFT JOIN usuario u ON b.id_usuario = u.id_usuario 
            ORDER BY b.fecha_acceso DESC";
    $stmt = $conexion->prepare($sql);
    $stmt->execute();
    $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Error al cargar la bitácora: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Bitácora de Accesos</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light"> <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4 shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="#"><i class="bi bi-shield-check me-2"></i>Gestión de Accesos</a>
            <div class="d-flex text-white align-items-center">
                <span class="me-3 d-none d-md-inline">Hola, <b><?php echo htmlspecialchars($_SESSION['nombre_completo']); ?></b></span>
                
                <button class="btn btn-sm btn-outline-light rounded-circle me-3" id="btn-theme" title="Cambiar Tema">
                    <i class="bi bi-moon-stars-fill" id="icon-theme"></i>
                </button>

                <a href="logout.php" class="btn btn-sm btn-danger"><i class="bi bi-box-arrow-right me-1"></i>Salir</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="card card-custom shadow-sm border-0 mb-5">
            <div class="card-header bg-transparent py-3 border-bottom">
                <h4 class="mb-0"><i class="bi bi-journal-text me-2 text-primary"></i>Bitácora de Accesos</h4>
            </div>
            <div class="card-body">
                
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Usuario</th>
                                <th>Fecha y Hora</th>
                                <th>Dirección IP</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(count($registros) > 0): ?>
                                <?php foreach($registros as $fila): ?>
                                <tr>
                                    <td><?php echo $fila['id_registro']; ?></td>
                                    <td>
                                        <?php echo $fila['nombre_completo'] ? htmlspecialchars($fila['nombre_completo']) : '<span class="text-muted fst-italic">Desconocido</span>'; ?>
                                    </td>
                                    <td><?php echo date('d/m/Y h:i:s A', strtotime($fila['fecha_acceso'])); ?></td>
                                    <td><?php echo $fila['direccion_ip']; ?></td>
                                    <td>
                                        <?php if($fila['exito'] == 1): ?>
                                            <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Correcto</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Fallido</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">No hay registros en la bitácora.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

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
                
                    btnTheme.classList.replace('btn-outline-light', 'btn-warning');
                    btnTheme.classList.replace('text-light', 'text-dark');
                } else {
                    iconTheme.classList.replace('bi-sun-fill', 'bi-moon-stars-fill');
                    btnTheme.classList.replace('btn-warning', 'btn-outline-light');
                    btnTheme.classList.replace('text-dark', 'text-light');
                }
            }
        });
    </script>
</body>
</html>