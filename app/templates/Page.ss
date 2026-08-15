<!DOCTYPE html>
<html lang="de">
    <head>
        <% base_tag %>
        $MetaTags(false)
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta charset="utf-8">
        <title>Photobox</title>
        $ViteClient.RAW
        <link rel="stylesheet" href="$Vite("app/client/src/scss/main.scss")">
    </head>
    <body>
        $Layout
        <script type="module" src="$Vite("app/client/src/js/main.js")"></script>
    </body>
</html>
