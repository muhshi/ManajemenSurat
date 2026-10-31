<div class="mt-4">
    <x-filament::button
        href="{{ route('sipetra.login') }}"
        tag="a"
        color="info"
        outlined
        icon="heroicon-m-arrow-right-on-rectangle"
        full-width
        onclick="if(this.getAttribute('data-submitting')) return false; this.setAttribute('data-submitting', '1'); this.style.pointerEvents='none'; this.style.opacity='0.75';"
    >
        Login dengan SIPETRA
    </x-filament::button>
</div>
