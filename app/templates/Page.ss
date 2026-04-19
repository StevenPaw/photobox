<!DOCTYPE html>
<html lang="de">
<head>
    <% base_tag %>
    $MetaTags(false)
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta charset="utf-8">
    <title>Photobox</title>
<%--    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">--%>
<%--    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">--%>
<%--    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">--%>
<%--    <link rel="manifest" href="/site.webmanifest">--%>
<%--    <link rel="mask-icon" href="/safari-pinned-tab.svg" color="#1e3755">--%>
<%--    <meta name="msapplication-TileColor" content="#ffffff">--%>
<%--    <meta name="theme-color" content="#ffffff">--%>
    $ViteClient.RAW
    <link rel="stylesheet" href="$Vite("app/client/src/scss/main.scss")">
</head>
<body>
<% include Header %>
$Layout
<% include Footer %>
<script type="module" src="$Vite("app/client/src/js/main.js")"></script>
</body>
</html>
