<!DOCTYPE html>
<html lang="de">
    <head>
        <% base_tag %>
        $MetaTags(false)
        <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta charset="utf-8">
        <title>Photobox</title>

        <!-- PWA Meta Tags -->
        <meta name="theme-color" content="#4E9DAE">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black-fullscreen">
        <meta name="apple-mobile-web-app-title" content="Photobox">
        <meta name="mobile-web-app-capable" content="yes">

        <!-- PWA Manifest -->
        <link rel="manifest" href="/manifest.json">

        <!-- Kamera-Berechtigung -->
        <meta http-equiv="Permissions-Policy" content="camera=*, microphone=*, geolocation=*">

        $ViteClient.RAW
        <link rel="stylesheet" href="$Vite('app/client/src/scss/main.scss')">
        <script type="module" src="$Vite('app/client/src/js/main.js')"></script>

        <meta name="twitter:card" content="summary" />
        <meta name="twitter:site" content="{$Title}" />
        <meta name="twitter:url" content="{$Link}" />
        <meta name="twitter:title" content="{$Title} - {$SiteConfig.Title}" />
        <meta name="twitter:description" content="{$Title}" />
        <meta name="twitter:image" content="_resources/app/client/images/SocialImage.png" />

        <meta property="og:type" content="website" />
        <meta property="og:title" content="{$Title} - {$SiteConfig.Title}" />
        <meta property="og:description" content="{$Title}" />
        <meta property="og:site_name" content="{$SiteConfig.Title}" />
        <meta property="og:url" content="{$Link}" />
        <meta property="og:image" content="_resources/app/client/images/SocialImage.png" />

        <link rel="apple-touch-icon" sizes="120x120" href="_resources/app/client/icons/apple-touch-icon_120.png" />
        <link rel="apple-touch-icon" sizes="180x180" href="_resources/app/client/icons/apple-touch-icon_180.png" />
        <link rel="mask-icon" href="_resources/app/client/icons/safari-pinned-tab.svg" color="#4E9DAE" />
        <meta name="msapplication-TileColor" content="#d54f27" />
        <link rel="apple-touch-icon" sizes="180x180" href="_resources/app/client/icons/safari-pinned-tab.svg" />
        <link rel="icon" type="image/png" sizes="32x32" href="_resources/app/client/icons/favicon-32.png" />
        <link rel="icon" type="image/png" sizes="16x16" href="_resources/app/client/icons/favicon-16.png" />
    </head>
    <body>
        <!-- Vue Root Element -->
        <div id="vue-root"></div>

        <!-- Fallback für Browser ohne JavaScript -->
        <noscript>
            <div style="padding: 2rem; text-align: center;">
                <h1>JavaScript erforderlich</h1>
                <p>Bitte aktiviere JavaScript, um diese App zu nutzen.</p>
            </div>
        </noscript>
    </body>
</html>
