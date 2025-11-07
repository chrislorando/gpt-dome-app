
<x-layouts.app.full :title="$title ?? null">
    <!-- Main Content -->
    <flux:main class="overflow-y-auto p-6">
        {{ $slot }}
    </flux:main>
</x-layouts.app.full>
