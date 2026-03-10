<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - BIENIESTAR</title>
    <link rel="icon" type="image/svg+xml" href="<?php echo asset('img/content/AAX-Form-Grafico.svg'); ?>">
    <link rel="stylesheet" href="<?php echo asset('css/main.css'); ?>">
    <style>
        .error-page {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 80vh;
            text-align: center;
            padding: 40px 20px;
        }
        .error-code {
            font-size: 8rem;
            font-weight: 800;
            color: #ff6b35;
            line-height: 1;
            margin-bottom: 20px;
        }
        .error-page h2 {
            font-size: 1.8rem;
            color: #333;
            margin-bottom: 15px;
        }
        .error-page p {
            color: #666;
            font-size: 1.1rem;
            margin-bottom: 30px;
        }
        .error-page .btn {
            display: inline-block;
            padding: 12px 30px;
            background: #ff6b35;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: background 0.2s;
        }
        .error-page .btn:hover {
            background: #e55a00;
        }
        [data-theme="dark"] .error-page h2 { color: #e8e8e8; }
        [data-theme="dark"] .error-page p { color: #aaa; }
    </style>
</head>
<body>
    <div class="error-page">
        <div class="error-code">404</div>
        <h2>Pagina no encontrada</h2>
        <p>La pagina que buscas no existe o fue movida.</p>
        <a href="<?php echo url(''); ?>" class="btn">Volver al inicio</a>
    </div>
</body>
</html>
