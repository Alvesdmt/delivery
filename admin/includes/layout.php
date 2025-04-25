<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Administrativo</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4A6CF7;
            --primary-dark: #2048E7;
            --primary-light: #6B89F9;
            --sidebar-width: 250px;
            --header-height: 60px;
            --border-radius: 12px;
            --footer-height: 60px;
            --sidebar-bg: linear-gradient(180deg, #1A237E 0%, #283593 100%);
            --sidebar-hover: rgba(255, 255, 255, 0.1);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', sans-serif;
        }

        body {
            background-color: #f5f6f8;
            display: flex;
            min-height: 100vh;
            position: relative;
        }

        /* Sidebar com novo design */
        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--sidebar-bg);
            padding: 20px;
            position: fixed;
            left: 0;
            top: 0;
            box-shadow: 4px 0 15px rgba(0, 0, 0, 0.1);
            transition: var(--transition);
            display: flex;
            flex-direction: column;
            z-index: 1000;
            color: white;
        }

        .sidebar-logo {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 10px;
            margin-bottom: 30px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-logo img {
            width: 40px;
            height: 40px;
            object-fit: contain;
            filter: brightness(0) invert(1);
        }

        .sidebar-logo h2 {
            color: white;
            font-size: 1.5rem;
            font-weight: 600;
            opacity: 0.95;
        }

        .nav-item {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            margin: 8px 0;
            border-radius: var(--border-radius);
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
            backdrop-filter: blur(5px);
        }

        .nav-item:hover {
            background: var(--sidebar-hover);
            color: white;
            transform: translateX(5px);
        }

        .nav-item.active {
            background: var(--primary-color);
            color: white;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .nav-item i {
            margin-right: 12px;
            font-size: 20px;
            width: 24px;
            text-align: center;
            transition: var(--transition);
        }

        .nav-item:hover i {
            transform: scale(1.1);
        }

        .nav-item span {
            font-weight: 500;
            letter-spacing: 0.3px;
        }

        .nav-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 3px;
            background: white;
            transform: scaleY(0);
            transition: var(--transition);
        }

        .nav-item:hover::before {
            transform: scaleY(1);
        }

        /* Efeito de brilho ao passar o mouse */
        .nav-item::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            transform: scale(0);
            transition: var(--transition);
        }

        .nav-item:hover::after {
            transform: scale(1);
        }

        /* Separador entre grupos de menu */
        .nav-separator {
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            margin: 15px 0;
        }

        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            flex: 1;
            padding: 20px;
            padding-top: calc(var(--header-height) + 20px);
            padding-bottom: calc(var(--footer-height) + 20px);
            transition: var(--transition);
        }

        /* Header */
        .header {
            position: fixed;
            top: 0;
            right: 0;
            left: var(--sidebar-width);
            height: var(--header-height);
            background: #fff;
            padding: 0 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            z-index: 100;
        }

        .search-bar {
            flex: 1;
            max-width: 500px;
            margin: 0 20px;
        }

        .search-bar input {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #e0e0e0;
            border-radius: var(--border-radius);
            background: #f5f6f8;
            font-size: 14px;
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e0e0e0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Cards e Grids */
        .grid-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .card {
            background: #fff;
            border-radius: var(--border-radius);
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        /* Quick Access */
        .quick-access {
            display: flex;
            gap: 15px;
            margin: 20px 0;
            flex-wrap: wrap;
        }

        .quick-access-item {
            background: #fff;
            border-radius: var(--border-radius);
            padding: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 120px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .quick-access-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        /* Footer */
        .footer {
            position: fixed;
            bottom: 0;
            right: 0;
            left: var(--sidebar-width);
            height: var(--footer-height);
            background: #fff;
            padding: 0 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.05);
            z-index: 99;
        }

        .footer-content {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .footer-links {
            display: flex;
            gap: 15px;
        }

        .footer-links a {
            color: #666;
            text-decoration: none;
            font-size: 14px;
            transition: color 0.3s ease;
        }

        .footer-links a:hover {
            color: var(--primary-color);
        }

        .footer-copyright {
            color: #888;
            font-size: 14px;
        }

        /* Responsividade */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .header, .footer {
                left: 0;
            }

            .search-bar {
                display: none;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="sidebar-logo">
            <img src="../assets/images/logo.png" alt="Logo">
            <h2>Admin Panel</h2>
        </div>
        
        <a href="#" class="nav-item active">
            <i class="fas fa-home"></i>
            <span>Dashboard</span>
        </a>
        <a href="#" class="nav-item">
            <i class="fas fa-box"></i>
            <span>Produtos</span>
        </a>
        <a href="#" class="nav-item">
            <i class="fas fa-users"></i>
            <span>Clientes</span>
        </a>

        <div class="nav-separator"></div>

        <a href="#" class="nav-item">
            <i class="fas fa-chart-bar"></i>
            <span>Relatórios</span>
        </a>
        <a href="#" class="nav-item">
            <i class="fas fa-cog"></i>
            <span>Configurações</span>
        </a>
    </nav>

    <!-- Header -->
    <header class="header">
        <div class="search-bar">
            <input type="text" placeholder="Pesquisar...">
        </div>
        <div class="user-menu">
            <div class="notifications">
                <i class="far fa-bell"></i>
            </div>
            <div class="user-avatar">
                <i class="fas fa-user"></i>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <h1>Dashboard</h1>
        
        <!-- Quick Access -->
        <div class="quick-access">
            <div class="quick-access-item">
                <i class="fas fa-image"></i>
                <span>Imagens</span>
            </div>
            <div class="quick-access-item">
                <i class="fas fa-file"></i>
                <span>Documentos</span>
            </div>
            <div class="quick-access-item">
                <i class="fas fa-download"></i>
                <span>Downloads</span>
            </div>
            <div class="quick-access-item">
                <i class="fas fa-plus"></i>
                <span>Adicionar</span>
            </div>
        </div>

        <!-- Grid Container -->
        <div class="grid-container">
            <div class="card">
                <h3>Estatísticas</h3>
                <!-- Conteúdo do card -->
            </div>
            <div class="card">
                <h3>Atividades Recentes</h3>
                <!-- Conteúdo do card -->
            </div>
            <div class="card">
                <h3>Tarefas</h3>
                <!-- Conteúdo do card -->
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-links">
                <a href="#">Sobre</a>
                <a href="#">Suporte</a>
                <a href="#">Política de Privacidade</a>
            </div>
        </div>
        <div class="footer-copyright">
            © 2024 Painel Administrativo. Todos os direitos reservados.
        </div>
    </footer>

    <script>
        // Adicione seus scripts JavaScript aqui
        document.addEventListener('DOMContentLoaded', function() {
            // Exemplo de funcionalidade para toggle do sidebar em dispositivos móveis
            const toggleSidebar = () => {
                document.querySelector('.sidebar').classList.toggle('active');
            };
        });
    </script>
</body>
</html>
