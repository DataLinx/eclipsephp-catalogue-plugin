<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Eclipse Catalogue Plugin Workbench</title>
        <style>
            body {
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                margin: 0;
                padding: 0;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .container {
                text-align: center;
                color: white;
                max-width: 600px;
                padding: 2rem;
            }
            h1 {
                font-size: 3rem;
                margin-bottom: 1rem;
                font-weight: 300;
            }
            p {
                font-size: 1.2rem;
                margin-bottom: 2rem;
                opacity: 0.9;
            }
            .links {
                display: flex;
                gap: 1rem;
                justify-content: center;
                flex-wrap: wrap;
            }
            .btn {
                display: inline-block;
                padding: 12px 24px;
                background: rgba(255, 255, 255, 0.1);
                color: white;
                text-decoration: none;
                border-radius: 25px;
                border: 1px solid rgba(255, 255, 255, 0.2);
                transition: all 0.3s ease;
            }
            .btn:hover {
                background: rgba(255, 255, 255, 0.2);
                transform: translateY(-2px);
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>Eclipse Catalogue Plugin</h1>
            <p>Workbench environment for developing the Eclipse Catalogue Filament plugin.</p>
            <div class="links">
                <a href="/admin" class="btn">Admin Panel</a>
                <a href="https://github.com/DataLinx/eclipsephp-catalogue-plugin" class="btn" target="_blank">GitHub</a>
            </div>
        </div>
    </body>
</html>
