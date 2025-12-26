<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width,initial-scale=1"/>
    <title>💻 Web Terminal</title>
    <link rel="stylesheet" href="../asset/css/main.css"/>
    <script type="module" src="../asset/js/main.js"></script>
</head>
<body>
<div class="window" id="win">
    <div class="titlebar">
        <div class="dots" aria-hidden="true">
            <span class="dot red"></span>
            <span class="dot yellow"></span>
            <span class="dot green"></span>
        </div>
        <div class="title">💻 web-terminal — localhost</div>
        <div class="hint">Tip:
            <span class="kbd">help</span>,
            <span class="kbd">↑</span>/<span class="kbd">↓</span>,
            <span class="kbd">Ctrl</span>+<span class="kbd">L</span>
        </div>
    </div>

    <div class="terminal" id="term" role="log" aria-live="polite"></div>

    <div class="footer">
        <div><span class="muted">Fake shell</span> · keine echten OS-Commands</div>
        <div class="muted">theme: <span id="themeName">dark</span></div>
    </div>
</div>

</body>
</html>
