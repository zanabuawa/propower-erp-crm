<section>
    <div class="overflow-hidden rounded-3xl border border-slate-200/60 bg-white shadow-sm"
        x-data="{
            drawing: false,
            hasInk: false,
            initCanvas() {
                const canvas = this.$refs.sigCanvasProfile;
                const input = this.$refs.signatureInput;
                if (!canvas || canvas._initialized) return;
                canvas._initialized = true;

                const ctx = canvas.getContext('2d');
                ctx.strokeStyle = '#312e81';
                ctx.lineWidth = 3;
                ctx.lineCap = 'round';
                ctx.lineJoin = 'round';

                let points = [];
                const smooth = 0.35;
                const pos = (event) => {
                    const rect = canvas.getBoundingClientRect();
                    const source = event.touches ? event.touches[0] : event;

                    return {
                        x: (source.clientX - rect.left) * (canvas.width / rect.width),
                        y: (source.clientY - rect.top) * (canvas.height / rect.height),
                    };
                };
                const lerp = (a, b, amount) => a + (b - a) * amount;
                const saveToInput = () => {
                    input.value = canvas.toDataURL('image/png');
                    this.hasInk = true;
                };
                const start = (event) => {
                    event.preventDefault();
                    this.drawing = true;
                    points = [];
                    const point = pos(event);
                    points.push(point);
                    ctx.beginPath();
                    ctx.moveTo(point.x, point.y);
                };
                const move = (event) => {
                    if (!this.drawing) return;
                    event.preventDefault();
                    const point = pos(event);
                    const prev = points[points.length - 1];
                    const current = {
                        x: lerp(prev.x, point.x, 1 - smooth),
                        y: lerp(prev.y, point.y, 1 - smooth),
                    };

                    points.push(current);

                    if (points.length >= 3) {
                        const p0 = points[points.length - 3];
                        const p1 = points[points.length - 2];
                        const p2 = points[points.length - 1];
                        const mid01 = { x: (p0.x + p1.x) / 2, y: (p0.y + p1.y) / 2 };
                        const mid12 = { x: (p1.x + p2.x) / 2, y: (p1.y + p2.y) / 2 };

                        ctx.beginPath();
                        ctx.moveTo(mid01.x, mid01.y);
                        ctx.quadraticCurveTo(p1.x, p1.y, mid12.x, mid12.y);
                        ctx.stroke();
                    } else {
                        ctx.beginPath();
                        ctx.moveTo(prev.x, prev.y);
                        ctx.lineTo(current.x, current.y);
                        ctx.stroke();
                    }

                    saveToInput();
                };
                const stop = () => {
                    if (this.drawing && points.length >= 2) {
                        const last = points[points.length - 1];
                        ctx.lineTo(last.x, last.y);
                        ctx.stroke();
                        saveToInput();
                    }

                    this.drawing = false;
                    points = [];
                };

                canvas.addEventListener('mousedown', start);
                canvas.addEventListener('mousemove', move);
                canvas.addEventListener('mouseup', stop);
                canvas.addEventListener('mouseleave', stop);
                canvas.addEventListener('touchstart', start, { passive: false });
                canvas.addEventListener('touchmove', move, { passive: false });
                canvas.addEventListener('touchend', stop);
            },
            clearCanvas() {
                const canvas = this.$refs.sigCanvasProfile;
                if (canvas) canvas.getContext('2d').clearRect(0, 0, canvas.width, canvas.height);
                this.$refs.signatureInput.value = '';
                this.hasInk = false;
            },
        }"
        x-init="initCanvas()">
        <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50/30 px-6 py-5">
            <h3 class="text-xs font-bold uppercase tracking-widest text-slate-500">Firma Autografa</h3>
            @if ($user->signature)
                <span class="rounded-lg bg-emerald-50 px-2.5 py-1 text-[10px] font-black uppercase tracking-wider text-emerald-600">Registrada</span>
            @else
                <span class="rounded-lg bg-amber-50 px-2.5 py-1 text-[10px] font-black uppercase tracking-wider text-amber-600">Pendiente</span>
            @endif
        </div>

        <div class="space-y-6 p-6 lg:p-8">
            @if (session('status') === 'signature-updated')
                <div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2400)" class="flex items-center gap-3 rounded-2xl border border-emerald-100 bg-emerald-50 p-4 text-emerald-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <p class="text-sm font-bold">Firma guardada correctamente.</p>
                </div>
            @endif

            @if (session('status') === 'signature-deleted')
                <div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2400)" class="flex items-center gap-3 rounded-2xl border border-amber-100 bg-amber-50 p-4 text-amber-700">
                    <p class="text-sm font-bold">Firma eliminada.</p>
                </div>
            @endif

            @if ($user->signature)
                <div class="space-y-2">
                    <p class="ml-1 text-[10px] font-black uppercase tracking-widest text-slate-400">Firma actual en sistema</p>
                    <div class="flex min-h-[100px] items-center justify-center rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <img src="{{ $user->signature }}" alt="Firma actual" class="max-h-20 object-contain drop-shadow-sm">
                    </div>
                    <p class="text-center text-[10px] font-semibold text-slate-400">
                        Ultima actualizacion: {{ $user->signature_updated_at?->format('d/m/Y H:i') ?? 'N/A' }}
                    </p>
                </div>
            @endif

            <form method="post" action="{{ route('profile.signature.update') }}" class="space-y-3 border-t border-slate-100 pt-4">
                @csrf
                @method('put')

                <input x-ref="signatureInput" type="hidden" name="signature">

                <div class="flex items-center justify-between px-1">
                    <label class="text-[11px] font-bold uppercase tracking-widest text-slate-400">
                        {{ $user->signature ? 'Actualizar firma' : 'Dibujar nueva firma' }}
                    </label>
                    <button type="button" x-on:click="clearCanvas()" class="text-[10px] font-black uppercase tracking-tighter text-red-400 transition-colors hover:text-red-600">
                        Limpiar Lienzo
                    </button>
                </div>

                <div class="cursor-crosshair overflow-hidden rounded-3xl border-2 border-dashed border-slate-200 bg-slate-50 transition-colors hover:border-indigo-300" style="touch-action: none;">
                    <canvas x-ref="sigCanvasProfile" width="800" height="180" class="block w-full" style="touch-action: none;"></canvas>
                </div>
                <x-input-error class="mt-2" :messages="$errors->signature->get('signature')" />

                <div class="flex items-center gap-3">
                    <button type="submit"
                        class="flex-1 rounded-xl bg-slate-800 py-3 text-[11px] font-black uppercase text-white shadow-lg shadow-slate-200 transition-all hover:bg-slate-900">
                        Guardar Firma
                    </button>
                </div>
            </form>

            @if ($user->signature)
                <form method="post" action="{{ route('profile.signature.destroy') }}">
                    @csrf
                    @method('delete')
                    <button type="submit"
                            onclick="return confirm('Eliminar definitivamente la firma?')"
                            class="rounded-xl border border-red-100 px-4 py-3 text-red-500 transition-colors hover:bg-red-50">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </form>
            @endif
        </div>
    </div>
</section>
