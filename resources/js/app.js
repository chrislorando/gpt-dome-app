// Copy code to clipboard function for markdown code blocks
window.copyCode = function(btn) {
    const codeBlock = btn.closest('.bg-zinc-900').querySelector('code');
    navigator.clipboard.writeText(codeBlock.innerText).then(() => {
        const originalText = btn.innerText;
        btn.innerText = 'Copied!';
        btn.classList.add('bg-green-600');
        setTimeout(() => {
            btn.innerText = originalText;
            btn.classList.remove('bg-green-600');
        }, 2000);
    }).catch(err => {
        console.error('Failed to copy:', err);
    });
};
