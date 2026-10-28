<?php
// Session defensiva (por si el padre no la inició)

// ⚠️ IMPORTANTE: el archivo padre DEBE haber iniciado sesión con requireLogin().
// Acá solo arrancamos si todavía no se arrancó Y si no se envió output todavía.
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}

$base = (basename(getcwd()) === 'php') ? '../' : '';

// ¿Fue abierto en nueva pestaña desde el index?
$es_nueva_pestana = isset($_GET['nueva']) && $_GET['nueva'] == '1';

// Helper para propagar ?nueva=1 en los enlaces
$param_nueva = $es_nueva_pestana ? '?nueva=1' : '';
$param_nueva_amp = $es_nueva_pestana ? '&nueva=1' : '';

// Usuario logueado (para mostrar en el nav)
$__user_nombre = $_SESSION['nombre'] ?? ($_SESSION['usuario'] ?? 'Invitado');
$__logueado = isset($_SESSION['id_usuario']);
?>

<nav class="top-nav">
    <a href="<?php echo $base; ?>reca.php" class="nav-brand"
        <?php if ($es_nueva_pestana): ?>onclick="cerrarSiEsNueva(event); return false;" <?php endif; ?>>
        <i class="fas fa-taxi"></i> Sistema Taxis
    </a>

    <div class="nav-links">
        <a href="taxis.php<?php echo $param_nueva; ?>"><i class="fas fa-users"></i> Choferes</a>
        <a href="chofer_nuevo.php<?php echo $param_nueva; ?>"><i class="fas fa-user-plus"></i> Nuevo Chofer</a>
        <a href="vehiculo_lista.php<?php echo $param_nueva; ?>"><i class="fas fa-car-side"></i> Vehículos</a>
        <a href="cargar_selector.php<?php echo $param_nueva; ?>"><i class="fas fa-dollar-sign"></i> Cargar</a>
    </div>

    <div class="nav-user">
        <?php if ($__logueado): ?>
            <span class="nav-user-name" title="Sesión activa">
                <i class="fas fa-user-circle"></i>
                <?php echo htmlspecialchars($__user_nombre); ?>
            </span>
            <a href="<?php echo $base; ?>cerrar_sesion.php" class="nav-logout" title="Cerrar sesión">
                <i class="fas fa-sign-out-alt"></i> Salir
            </a>
       
        <?php endif; ?>
    </div>
</nav>

<?php if ($es_nueva_pestana): ?>
    <script>
        function cerrarSiEsNueva(event) {
            if (event) event.preventDefault();

            console.log('Intentando cerrar. Opener:', window.opener);

            // Intento 1: cerrar normalmente (funciona si window.open() abrió la pestaña)
            window.close();

            // Intento 2: si después de 300ms seguimos acá, el navegador bloqueó el cierre
            setTimeout(function() {
                // Si todavía estamos acá, redirigimos al dashboard como fallback
                console.log('window.close() bloqueado, redirigiendo al dashboard...');
                window.location.href = '<?php echo $base; ?>reca.php';
            }, 300);
        }
    </script>
<?php endif; ?>

<style>
    .top-nav {
        background: linear-gradient(135deg, #667eea, #764ba2);
        padding: 12px 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        font-family: 'Segoe UI', Arial, sans-serif;
        position: sticky;
        top: 0;
        z-index: 100;
        gap: 15px;
    }

    .top-nav .nav-brand {
        color: white;
        font-size: 1.2rem;
        font-weight: 600;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 8px;
        white-space: nowrap;
    }

    .top-nav .nav-brand:hover {
        color: white;
        text-decoration: none;
        opacity: 0.9;
    }

    .top-nav .nav-links {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
        flex: 1;
    }

    .top-nav .nav-links a {
        color: rgba(255, 255, 255, 0.9);
        text-decoration: none;
        padding: 8px 14px;
        border-radius: 6px;
        font-size: 0.9rem;
        transition: background 0.2s;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .top-nav .nav-links a:hover {
        background: rgba(255, 255, 255, 0.2);
        color: white;
        text-decoration: none;
    }

    /* === NUEVO: bloque de usuario + logout === */
    .top-nav .nav-user {
        display: flex;
        align-items: center;
        gap: 8px;
        padding-left: 10px;
        border-left: 1px solid rgba(255, 255, 255, 0.3);
    }

    .top-nav .nav-user-name {
        color: white;
        font-size: 0.85rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 6px 10px;
        background: rgba(255, 255, 255, 0.15);
        border-radius: 20px;
        white-space: nowrap;
    }

    .top-nav .nav-user-name i {
        font-size: 1rem;
        opacity: 0.9;
    }

    .top-nav .nav-logout {
        background: rgba(220, 53, 69, 0.85);
        color: white;
        padding: 7px 14px;
        border-radius: 6px;
        text-decoration: none;
        font-size: 0.85rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 6px;
        transition: background 0.2s, transform 0.15s;
        white-space: nowrap;
    }

    .top-nav .nav-logout:hover {
        background: rgba(220, 53, 69, 1);
        color: white;
        text-decoration: none;
        transform: translateY(-1px);
    }

    .top-nav .nav-home {
        color: white;
        background: rgba(255, 255, 255, 0.2);
        padding: 8px 14px;
        border-radius: 6px;
        text-decoration: none;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        gap: 6px;
        white-space: nowrap;
    }

    .top-nav .nav-home:hover {
        background: rgba(255, 255, 255, 0.35);
        color: white;
        text-decoration: none;
    }

    @media (max-width: 900px) {
        .top-nav {
            flex-wrap: wrap;
            padding: 10px 15px;
        }

        .top-nav .nav-links {
            order: 3;
            width: 100%;
            justify-content: center;
            margin-top: 5px;
        }

        .top-nav .nav-user {
            margin-left: auto;
        }
    }

    @media (max-width: 600px) {
        .top-nav .nav-user-name {
            display: none;
        }

        .top-nav .nav-links a {
            font-size: 0.8rem;
            padding: 6px 10px;
        }
    }
</style>