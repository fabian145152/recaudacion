<?php
$base = (basename(getcwd()) === 'php') ? '../' : '';

// ¿Fue abierto en nueva pestaña desde el index?
$es_nueva_pestana = isset($_GET['nueva']) && $_GET['nueva'] == '1';

// Helper para propagar ?nueva=1 en los enlaces
$param_nueva = $es_nueva_pestana ? '?nueva=1' : '';
$param_nueva_amp = $es_nueva_pestana ? '&nueva=1' : '';
?>

<nav class="top-nav">
    <a href="<?php echo $base; ?>index.php" class="nav-brand"
        <?php if ($es_nueva_pestana): ?>onclick="cerrarSiEsNueva(event); return false;" <?php endif; ?>>
        <i class="fas fa-taxi"></i> Sistema Taxis
    </a>

    <div class="nav-links">
        <a href="taxis.php<?php echo $param_nueva; ?>"><i class="fas fa-users"></i> Choferes</a>
        <a href="chofer_nuevo.php<?php echo $param_nueva; ?>"><i class="fas fa-user-plus"></i> Nuevo Chofer</a>
        <a href="vehiculo_lista.php<?php echo $param_nueva; ?>"><i class="fas fa-car-side"></i> Vehículos</a>
        <a href="cargar_selector.php<?php echo $param_nueva; ?>"><i class="fas fa-dollar-sign"></i> Cargar</a>
        <a href="cuenta.php<?php echo $param_nueva; ?>"><i class="fas fa-wallet"></i> Cuenta</a>
        <a href="ver_recauda.php<?php echo $param_nueva; ?>"><i class="fas fa-chart-line"></i> Planilla</a>
    </div>

    <a href="<?php echo $base; ?>index.php" class="nav-home"
        <?php if ($es_nueva_pestana): ?>onclick="cerrarSiEsNueva(event); return false;" <?php endif; ?>>
        <i class="fas fa-home"></i> Inicio
    </a>
</nav>

<?php if ($es_nueva_pestana): ?>
    <script>
        function cerrarSiEsNueva(event) {
            if (event) event.preventDefault();

            // Verificar si esta pestaña fue abierta por script
            if (window.opener && !window.opener.closed) {
                window.close();
            } else {
                window.location.href = '<?php echo $base; ?>index.php';
            }
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
    }

    .top-nav .nav-brand {
        color: white;
        font-size: 1.2rem;
        font-weight: 600;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .top-nav .nav-links {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
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
    }

    .top-nav .nav-home:hover {
        background: rgba(255, 255, 255, 0.35);
        color: white;
        text-decoration: none;
    }

    @media (max-width: 768px) {
        .top-nav {
            flex-direction: column;
            gap: 10px;
        }

        .top-nav .nav-links {
            justify-content: center;
        }
    }
</style>