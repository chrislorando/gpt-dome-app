<div class="w-full">
    <svg viewBox="0 0 300 160" class="drop-shadow-lg w-full h-auto block" preserveAspectRatio="none">
        <rect x="0" y="0" width="300" height="160" rx="8" fill="#1f2937" class="dark:fill-zinc-800" />
        <rect x="5" y="5" width="290" height="150" rx="6" fill="#374151" class="dark:fill-zinc-700" />
        <rect x="15" y="15" width="270" height="60" rx="4" fill="#f3f4f6" class="dark:fill-zinc-600" />
        <text x="150" y="40" text-anchor="middle" class="fill-zinc-700 dark:fill-zinc-300 text-sm font-semibold">VOICE NOTE</text>
        <text x="150" y="60" text-anchor="middle" class="fill-zinc-500 dark:fill-zinc-400 text-xs">AI Transcription</text>
        
        <circle cx="70" cy="115" r="35" fill="#111827" class="dark:fill-zinc-900" />
        <circle cx="70" cy="115" r="30" fill="#1f2937" class="dark:fill-zinc-800" />
        <g class="cassette-reel" style="transform-origin: 70px 115px">
            <circle cx="70" cy="115" r="8" fill="#4b5563" class="dark:fill-zinc-600" />
            <line x1="70" y1="87" x2="70" y2="100" stroke="#4b5563" stroke-width="2" />
            <line x1="70" y1="130" x2="70" y2="143" stroke="#4b5563" stroke-width="2" />
            <line x1="42" y1="115" x2="55" y2="115" stroke="#4b5563" stroke-width="2" />
            <line x1="85" y1="115" x2="98" y2="115" stroke="#4b5563" stroke-width="2" />
        </g>
        
        <circle cx="230" cy="115" r="35" fill="#111827" class="dark:fill-zinc-900" />
        <circle cx="230" cy="115" r="30" fill="#1f2937" class="dark:fill-zinc-800" />
        <g class="cassette-reel" style="transform-origin: 230px 115px">
            <circle cx="230" cy="115" r="8" fill="#4b5563" class="dark:fill-zinc-600" />
            <line x1="230" y1="87" x2="230" y2="100" stroke="#4b5563" stroke-width="2" />
            <line x1="230" y1="130" x2="230" y2="143" stroke="#4b5563" stroke-width="2" />
            <line x1="202" y1="115" x2="215" y2="115" stroke="#4b5563" stroke-width="2" />
            <line x1="245" y1="115" x2="258" y2="115" stroke="#4b5563" stroke-width="2" />
        </g>
        
        <rect x="105" y="105" width="90" height="20" rx="2" fill="#6b7280" opacity="0.3" />
        
        <circle cx="20" cy="20" r="3" fill="#9ca3af" />
        <circle cx="280" cy="20" r="3" fill="#9ca3af" />
        <circle cx="20" cy="145" r="3" fill="#9ca3af" />
        <circle cx="280" cy="145" r="3" fill="#9ca3af" />
    </svg>

    <style>
        @keyframes spin-slow {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        .playing .cassette-reel {
            animation: spin-slow 2s linear infinite;
        }
    </style>
</div>