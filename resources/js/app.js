// Copy code to clipboard function for markdown code blocks
window.copyCode = function(btn) {
    const preElement = btn.closest('pre');
    const codeBlock = preElement.querySelector('code');
    const code = codeBlock ? codeBlock.innerText : '';
    
    navigator.clipboard.writeText(code).then(() => {
        const originalText = btn.innerText;
        btn.innerText = 'Copied!';
        btn.classList.add('success');
        
        setTimeout(() => {
            btn.innerText = originalText;
            btn.classList.remove('success');
        }, 2000);
    }).catch(err => {
        console.error('Failed to copy:', err);
    });
};

// Add copy buttons to shiki code blocks
function addCopyButtonsToCodeBlocks() {
    document.querySelectorAll('.shiki pre').forEach(preElement => {
        if (!preElement.querySelector('.copy-btn')) {
            const copyBtn = document.createElement('button');
            copyBtn.className = 'copy-btn';
            copyBtn.type = 'button';
            copyBtn.innerText = 'Copy';
            copyBtn.onclick = function(e) {
                e.preventDefault();
                window.copyCode(this);
            };
            
            preElement.appendChild(copyBtn);
        }
    });
}

// Run on page load
document.addEventListener('DOMContentLoaded', function() {
    addCopyButtonsToCodeBlocks();
});

// Also watch for dynamically added content (from Livewire streams)
const observer = new MutationObserver(function() {
    addCopyButtonsToCodeBlocks();
});

observer.observe(document.body, {
    childList: true,
    subtree: true,
});
