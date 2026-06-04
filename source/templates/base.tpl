<body>
    <div class="logo centerItem">
        <img width="100" height="100" src="{$STATIC_URL}image/logo.png">
        <h1>{$PAGE_TITLE}</h1>
        <p>{$MAIN_PAGE_SUBTITLE}</p>
    </div>
    <main>
        <div class="mainSectionContainer">
            {template:nav}
            {$CONTENT}
        </div>
    </main>
    {template:footer}
</body>
