<!DOCTYPE html>
<html lang="de">
<head>
    <% base_tag %>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Foto Download - {$EventTitle}</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            position: relative;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #000000 0%, #2b2b2b 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image: url('/bg.jpg');
            background-size: cover;
            background-position: center;
            opacity: 0.3;
            pointer-events: none;
            z-index: 0;
        }

        .download-container {
            position: relative;
            z-index: 1;
            background: rgba(40, 40, 40, 0.9);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 24px;
            padding: 3rem;
            max-width: 600px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4);
            text-align: center;
            color: white;
        }

        .event-header {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-wrap: wrap;
            gap: 0.6rem;
            margin-bottom: 0.5rem;
        }

        .event-title {
            color: white;
            font-size: 1.2rem;
            font-weight: 600;
        }

        .event-link-btn {
            display: inline-block;
            padding: 0.3rem 0.9rem;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
            text-decoration: none;
            border-radius: 999px;
            font-size: 0.8rem;
            font-weight: 500;
            transition: background 0.3s;
        }

        .event-link-btn:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .photo-date {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.9rem;
            margin-bottom: 2rem;
        }

        .photo-preview {
            width: 100%;
            border-radius: 16px;
            margin-bottom: 0;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        }

        .download-section {
            margin-top: 2rem;
        }

        .download-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 1rem 3rem;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            text-decoration: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4);
        }

        .download-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.6);
        }

        .download-icon {
            width: 1.3rem;
            height: 1.3rem;
        }

        .info-text {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.9rem;
            margin-top: 1.5rem;
            line-height: 1.5;
        }

        .not-found {
            padding: 4rem 2rem;
        }

        .not-found h1 {
            color: #ef4444;
            margin-bottom: 1rem;
        }

        .not-found p {
            color: rgba(255, 255, 255, 0.7);
        }

        @media (max-width: 640px) {
            .download-container {
                padding: 2rem;
            }

            .event-title {
                font-size: 1rem;
            }

            .download-btn {
                padding: 0.875rem 2rem;
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="download-container">
        <% if $Photo %>
            <div class="event-header">
                <span class="event-title">{$EventTitle}</span>
                <% if $EventLink %>
                    <% with $EventLink %>
                        <% if $exists %>
                            <a href="{$URL}" <% if $OpenInNew %>target="_blank" rel="noopener noreferrer"<% end_if %> class="event-link-btn">{$Title}</a>
                        <% end_if %>
                    <% end_with %>
                <% end_if %>
            </div>
            <div class="photo-date">{$PhotoDate} Uhr</div>

            <% if $ImageURL %>
                <img src="{$ImageURL}" alt="Dein Foto" class="photo-preview">

                <div class="download-section">
                    <a href="{$DownloadURL}" download="foto-{$Photo.Hash}.jpg" class="download-btn">
                        <img src="/action_download.svg" alt="" class="download-icon">
                        Foto herunterladen
                    </a>

                    <p class="info-text">
                        Dein Foto ist bereit zum Download!<br>
                        Klicke auf den Button, um es auf deinem Gerät zu speichern.
                    </p>
                </div>
            <% else %>
                <div class="not-found">
                    <h1>❌ Bild nicht verfügbar</h1>
                    <p>Das Bild konnte leider nicht geladen werden.</p>
                </div>
            <% end_if %>
        <% else %>
            <div class="not-found">
                <h1>🔍 Foto nicht gefunden</h1>
                <p>Das angeforderte Foto existiert nicht oder wurde gelöscht.</p>
            </div>
        <% end_if %>
    </div>
</body>
</html>
