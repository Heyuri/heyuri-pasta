document.addEventListener('DOMContentLoaded', function() {
    var btn = document.getElementById('copyBtn');
    if (!btn) return;

    btn.addEventListener('click', function() {
        var content = document.getElementById('pasteContent').innerText;
        navigator.clipboard.writeText(content).then(function() {
            btn.textContent = 'Copied!';
            setTimeout(function() { btn.textContent = 'Copy'; }, 2000);
        });
    });
});
