<?php

namespace App\Services;

class MarkdownParser
{
    public static function parse(string $content): string
    {
        // Store placeholders for code blocks and inline code
        $preservedContent = [];
        $counter = 0;

        // Extract code blocks with triple backticks (``` ... ```)
        $content = preg_replace_callback(
            '/```\s*([^\n]*?)\s*\n(.*?)```/s',
            function ($matches) use (&$preservedContent, &$counter) {
                $language = isset($matches[1]) ? trim($matches[1]) : '';
                $language = $language ?: 'plaintext';
                $code = $matches[2];
                $placeholder = "___PRESERVE_{$counter}___";
                $counter++;
                $escapedCode = htmlspecialchars($code, ENT_QUOTES, 'UTF-8');
                $codeBlock = '<div class="bg-zinc-900 rounded-lg my-1.5 overflow-hidden border border-zinc-700">
                    <div class="flex items-center justify-between px-4 py-2 border-b border-zinc-700 bg-zinc-800">
                        <span class="text-xs font-mono text-zinc-400">'.htmlspecialchars($language).'</span>
                        <button onclick="copyCode(this)" class="text-xs px-2 py-1 bg-zinc-700 hover:bg-zinc-600 text-zinc-100 rounded transition">Copy</button>
                    </div>
                    <pre class="p-4 overflow-x-auto text-sm text-zinc-100 whitespace-pre-wrap"><code class="language-'.htmlspecialchars($language).'">'.$escapedCode.'</code></pre>
                </div>';
                $preservedContent[$placeholder] = $codeBlock;

                return "\n".$placeholder."\n";
            },
            $content
        );

        // Extract inline code (` ... `) - single line only
        $content = preg_replace_callback(
            '/`([^`\n]+)`/',
            function ($matches) use (&$preservedContent, &$counter) {
                $code = $matches[1];
                $placeholder = "___PRESERVE_{$counter}___";
                $counter++;
                $preservedContent[$placeholder] = '<code class="bg-zinc-200 dark:bg-zinc-800 px-1.5 py-0.5 rounded text-sm font-mono">'.htmlspecialchars($code, ENT_QUOTES, 'UTF-8').'</code>';

                return $placeholder;
            },
            $content
        );

        // Now escape HTML
        $content = htmlspecialchars($content, ENT_QUOTES, 'UTF-8');

        // Bold (**text** or __text__)
        $content = preg_replace(
            '/\*\*([^\*\n]+)\*\*/',
            '<strong class="font-semibold">$1</strong>',
            $content
        );
        $content = preg_replace(
            '/__([^_\n]+)__/',
            '<strong class="font-semibold">$1</strong>',
            $content
        );

        // Italic (*text* or _text_)
        $content = preg_replace(
            '/(?<!\*)\*([^\*\n]+)\*(?!\*)/',
            '<em class="italic">$1</em>',
            $content
        );
        $content = preg_replace(
            '/(?<!_)_([^_\n]+)_(?!_)/',
            '<em class="italic">$1</em>',
            $content
        );

        // Headers (# Header)
        $content = preg_replace(
            '/^#### ([^\n]+)/m',
            '<h4 class="text-base font-semibold mt-1 mb-0.5">$1</h4>',
            $content
        );
        $content = preg_replace(
            '/^### ([^\n]+)/m',
            '<h3 class="text-lg font-semibold mt-1.5 mb-0.5">$1</h3>',
            $content
        );
        $content = preg_replace(
            '/^## ([^\n]+)/m',
            '<h2 class="text-xl font-bold mt-2 mb-1">$1</h2>',
            $content
        );
        $content = preg_replace(
            '/^# ([^\n]+)/m',
            '<h1 class="text-2xl font-bold mt-2 mb-1">$1</h1>',
            $content
        );

        // Ordered lists (1. item, 2. item, etc.)
        $content = preg_replace_callback(
            '/(?:^|\n)(\d+)\. ([^\n]+)(?:\n(?:\d+)\. [^\n]+)*/m',
            function ($matches) {
                $list = $matches[0];
                $items = preg_replace('/^(\d+)\. (.+)$/m', '<li class="ml-4 leading-7"><span class="ml-2">$2</span></li>', $list);

                return '<ol class="list-decimal">'.$items.'</ol>';
            },
            $content
        );

        // Unordered lists (- item or * item)
        $content = preg_replace_callback(
            '/(?:^|\n)[-\*] ([^\n]+)(?:\n[-\*] [^\n]+)*/m',
            function ($matches) {
                $list = $matches[0];
                $items = preg_replace('/^[-\*] (.+)$/m', '<li class="ml-4 leading-7"><span class="ml-2">$1</span></li>', $list);

                return '<ul class="list-disc my-1">'.$items.'</ul>';
            },
            $content
        );

        // Links [text](url)
        $content = preg_replace(
            '/\[([^\]]+)\]\(([^\)]+)\)/',
            '<a href="$2" class="text-blue-600 dark:text-blue-400 hover:underline" target="_blank" rel="noopener noreferrer">$1</a>',
            $content
        );

        // Blockquotes (> text)
        $content = preg_replace(
            '/^&gt; (.+)$/m',
            '<blockquote class="border-l-4 border-zinc-300 dark:border-zinc-700 pl-4 my-1 italic text-zinc-600 dark:text-zinc-400">$1</blockquote>',
            $content
        );

        // Horizontal rules (--- or ***)
        $content = preg_replace(
            '/^(?:---|\*\*\*)$/m',
            '<hr class="my-2 border-zinc-300 dark:border-zinc-700">',
            $content
        );

        // Split content into paragraphs by double newlines
        $paragraphs = preg_split('/\n\n+/', trim($content));

        // Process each paragraph
        $processedParagraphs = [];
        foreach ($paragraphs as $paragraph) {
            $paragraph = trim($paragraph);
            if (empty($paragraph)) {
                continue;
            }

            // Skip wrapping if it's already HTML (lists, blockquotes, headings, etc.)
            if (preg_match('/^<(ul|ol|h[1-6]|blockquote|hr|div)/', $paragraph)) {
                $processedParagraphs[] = $paragraph;
            } else {
                // Replace single newlines with spaces for normal text
                $paragraph = preg_replace('/\n/', ' ', $paragraph);
                $processedParagraphs[] = '<p>'.$paragraph.'</p>';
            }
        }

        $content = implode("\n", $processedParagraphs);

        // Clean up empty paragraphs
        $content = preg_replace('/<p>\s*<\/p>/', '', $content);

        // Restore preserved content (code blocks and inline code)
        foreach ($preservedContent as $placeholder => $code) {
            $content = str_replace($placeholder, $code, $content);
        }

        return $content;
    }
}
