@php
    $fieldWrapperView = $getFieldWrapperView();
    $statePath = $getStatePath();
    $padId = 'sig-pad-' . str_replace(['.', '[', ']'], '-', $statePath);
    $existingSignature = $getState();
    $isReadOnly = $isDisabled() || (method_exists($field, 'isViewMode') && $field->isViewMode());
@endphp

<x-dynamic-component :component="$fieldWrapperView" :field="$field">
    <div
        x-data="{
            isDrawing: false,
            isEmpty: true,
            canvas: null,
            ctx: null,
            lastX: 0,
            lastY: 0,

            init() {
                this.canvas = document.getElementById('{{ $padId }}');
                if (!this.canvas) return;
                this.ctx = this.canvas.getContext('2d');
                this.ctx.strokeStyle = '#1a1a1a';
                this.ctx.lineWidth = 2;
                this.ctx.lineCap = 'round';
                this.ctx.lineJoin = 'round';

                // Load existing signature if any
                const existing = @js($existingSignature);
                if (existing) {
                    const img = new Image();
                    img.onload = () => {
                        this.ctx.drawImage(img, 0, 0);
                        this.isEmpty = false;
                    };
                    img.src = existing;
                }

                @if(!$isReadOnly)
                this.canvas.addEventListener('mousedown', (e) => this.startDraw(e));
                this.canvas.addEventListener('mousemove', (e) => this.draw(e));
                this.canvas.addEventListener('mouseup', () => this.stopDraw());
                this.canvas.addEventListener('mouseleave', () => this.stopDraw());

                // Touch support
                this.canvas.addEventListener('touchstart', (e) => {
                    e.preventDefault();
                    this.startDraw(e.touches[0]);
                }, { passive: false });
                this.canvas.addEventListener('touchmove', (e) => {
                    e.preventDefault();
                    this.draw(e.touches[0]);
                }, { passive: false });
                this.canvas.addEventListener('touchend', () => this.stopDraw());
                @endif
            },

            getPos(e) {
                const rect = this.canvas.getBoundingClientRect();
                const scaleX = this.canvas.width / rect.width;
                const scaleY = this.canvas.height / rect.height;
                return {
                    x: (e.clientX - rect.left) * scaleX,
                    y: (e.clientY - rect.top) * scaleY
                };
            },

            startDraw(e) {
                this.isDrawing = true;
                const pos = this.getPos(e);
                this.lastX = pos.x;
                this.lastY = pos.y;
                this.ctx.beginPath();
                this.ctx.moveTo(pos.x, pos.y);
            },

            draw(e) {
                if (!this.isDrawing) return;
                const pos = this.getPos(e);
                this.ctx.lineTo(pos.x, pos.y);
                this.ctx.stroke();
                this.lastX = pos.x;
                this.lastY = pos.y;
                this.isEmpty = false;
            },

            stopDraw() {
                if (!this.isDrawing) return;
                this.isDrawing = false;
                this.ctx.closePath();
                this.saveSignature();
            },

            saveSignature() {
                if (this.isEmpty) return;
                const dataUrl = this.canvas.toDataURL('image/png');
                $wire.set('{{ $statePath }}', dataUrl);
            },

            clearSignature() {
                this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
                this.isEmpty = true;
                $wire.set('{{ $statePath }}', null);
            }
        }"
        style="width: 100%; max-width: 100%;"
    >
        {{-- Canvas container --}}
        <div style="position: relative; border: 1px solid #d1d5db; border-radius: 0.5rem; overflow: hidden; background: #fff;">
            <canvas
                id="{{ $padId }}"
                width="700"
                height="200"
                style="width: 100%; height: 200px; display: block; cursor: {{ $isReadOnly ? 'default' : 'crosshair' }}; touch-action: none;"
            ></canvas>

            {{-- Watermark text --}}
            @if(!$isReadOnly)
            <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); pointer-events: none; opacity: 0.15; font-size: 14px; color: #666; white-space: nowrap; user-select: none;">
                Tanda tangan di sini
            </div>
            @endif
        </div>

        {{-- Action buttons --}}
        @if(!$isReadOnly)
        <div style="display: flex; align-items: center; gap: 0.75rem; margin-top: 0.5rem;">
            <button
                type="button"
                x-on:click="clearSignature()"
                style="display: inline-flex; align-items: center; gap: 0.375rem; padding: 0.375rem 0.75rem; font-size: 0.875rem; font-weight: 500; color: #dc2626; background: #fef2f2; border: 1px solid #fecaca; border-radius: 0.5rem; cursor: pointer;"
                onmouseover="this.style.background='#fee2e2'" onmouseout="this.style.background='#fef2f2'"
            >
                <svg xmlns="http://www.w3.org/2000/svg" style="width: 1rem; height: 1rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 6h18M19 6l-1 14H6L5 6M8 6V4h8v2"/>
                </svg>
                Hapus Tanda Tangan
            </button>
            <span style="font-size: 0.75rem; color: #9ca3af;">Gunakan mouse atau jari untuk membuat tanda tangan</span>
        </div>
        @else
            {{-- View mode --}}
            @if($existingSignature)
            <div style="display: flex; align-items: center; gap: 0.25rem; margin-top: 0.5rem; font-size: 0.75rem; color: #6b7280;">
                <svg xmlns="http://www.w3.org/2000/svg" style="width: 1rem; height: 1rem; color: #22c55e;" viewBox="0 0 24 24" fill="currentColor">
                    <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12zm13.36-1.814a.75.75 0 10-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.75-5.25z" clip-rule="evenodd"/>
                </svg>
                Tanda tangan tersimpan
            </div>
            @else
            <div style="margin-top: 0.5rem; font-size: 0.75rem; color: #9ca3af;">Belum ada tanda tangan</div>
            @endif
        @endif
    </div>
</x-dynamic-component>
