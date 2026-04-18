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
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .download-container {
            background: white;
            border-radius: 24px;
            padding: 3rem;
            max-width: 600px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            text-align: center;
        }

        .event-title {
            color: #667eea;
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .photo-date {
            color: #6b7280;
            font-size: 0.9rem;
            margin-bottom: 2rem;
        }

        .photo-preview {
            width: 100%;
            border-radius: 16px;
            margin-bottom: 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .download-section {
            margin-top: 2rem;
        }

        .download-btn {
            display: inline-block;
            padding: 1rem 3rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }

        .download-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
        }

        .download-icon {
            margin-right: 0.5rem;
        }

        .info-text {
            color: #6b7280;
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
            color: #6b7280;
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
            <div class="event-title">📸 {$EventTitle}</div>
            <div class="photo-date">{$PhotoDate} Uhr</div>

            <% if $ImageURL %>
                <img src="{$ImageURL}" alt="Dein Foto" class="photo-preview">

                <div class="download-section">
                    <a href="{$DownloadURL}" download="foto-{$Photo.Hash}.jpg" class="download-btn">
                        <span class="download-icon">⬇️</span>
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
