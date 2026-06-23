<section class="space-y-6">
    <style>
        @media print {
            body * {
                visibility: hidden;
            }

            #two-factor-recovery-print-area,
            #two-factor-recovery-print-area * {
                visibility: visible;
            }

            #two-factor-recovery-print-area {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                border: 0 !important;
                background: #fff !important;
                color: #0f172a !important;
            }
        }
    </style>

    <header>
        <h2 class="text-lg font-bold text-gray-900">Autenticacion en dos pasos</h2>
        <p class="mt-1 text-sm text-gray-600">
            Protege tu cuenta con una app autenticadora. El sistema pedira este codigo despues de tu contrasena.
        </p>
    </header>

    @if (session('two_factor_status'))
        <div class="rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
            @switch(session('two_factor_status'))
                @case('two-factor-started')
                    Configuracion iniciada. Ingresa la clave en tu app y confirma el codigo.
                    @break
                @case('two-factor-enabled')
                    Autenticacion en dos pasos activada correctamente.
                    @break
                @case('two-factor-disabled')
                    Autenticacion en dos pasos desactivada.
                    @break
                @case('recovery-codes-regenerated')
                    Codigos de recuperacion regenerados.
                    @break
                @default
                    Configuracion actualizada.
            @endswitch
        </div>
    @endif

    @if (session('two_factor_recovery_codes_plain'))
        <div id="two-factor-recovery-print-area" class="rounded-2xl border border-amber-200 bg-amber-50 p-4">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p class="text-sm font-bold text-amber-900">Guarda estos codigos de recuperacion en un lugar seguro.</p>
                    <p class="mt-1 text-xs text-amber-700">Solo se mostraran esta vez y cada codigo se puede usar una sola vez.</p>
                </div>
                <button type="button" onclick="window.print()"
                    class="inline-flex items-center justify-center rounded-lg border border-amber-200 bg-white px-3 py-2 text-[10px] font-black uppercase tracking-widest text-amber-800 transition hover:bg-amber-100 print:hidden">
                    Imprimir codigos
                </button>
            </div>
            <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-2">
                @foreach(session('two_factor_recovery_codes_plain') as $code)
                    <code class="rounded-lg bg-white px-3 py-2 text-sm font-black text-slate-800 border border-amber-100">{{ $code }}</code>
                @endforeach
            </div>
            <div class="hidden print:block mt-6 border-t border-slate-200 pt-4 text-xs text-slate-600">
                <p><strong>Cuenta:</strong> {{ $user->email }}</p>
                <p><strong>Generado:</strong> {{ now()->format('d/m/Y H:i') }}</p>
                <p>Conserva estos codigos fuera del equipo. Cada codigo se puede usar una sola vez.</p>
            </div>
        </div>
    @endif

    @if (! $user->hasTwoFactorEnabled())
        @if (! $user->two_factor_pending_secret)
            <form method="POST" action="{{ route('profile.two-factor.start') }}">
                @csrf
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-slate-900 border border-transparent rounded-lg font-bold text-xs text-white uppercase tracking-widest hover:bg-slate-700 focus:bg-slate-700 active:bg-slate-900 transition">
                    Activar autenticacion
                </button>
            </form>
        @else
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 space-y-4">
                <div>
                    <p class="text-sm font-bold text-slate-800">Agrega esta cuenta en tu app autenticadora</p>
                    <p class="mt-1 text-xs text-slate-500">Puedes usar Google Authenticator, Microsoft Authenticator, Authy, 1Password o Bitwarden.</p>
                </div>

                <div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Clave manual</p>
                    <code class="block break-all rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-black text-slate-800">{{ $user->two_factor_pending_secret }}</code>
                </div>

                <div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">URI para apps compatibles</p>
                    <code class="block break-all rounded-xl border border-slate-200 bg-white px-4 py-3 text-xs font-semibold text-slate-600">{{ $twoFactorProvisioningUri }}</code>
                </div>

                <form method="POST" action="{{ route('profile.two-factor.confirm') }}" class="space-y-3">
                    @csrf
                    <div>
                        <label for="code" class="block text-sm font-medium text-gray-700">Codigo de 6 digitos</label>
                        <input id="code" name="code" inputmode="numeric" pattern="[0-9]*" autocomplete="one-time-code"
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            placeholder="000000">
                        <x-input-error :messages="$errors->get('code')" class="mt-2" />
                    </div>

                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-bold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 transition">
                        Confirmar y activar
                    </button>
                </form>
            </div>
        @endif
    @else
        <div class="rounded-2xl border border-emerald-100 bg-emerald-50 p-4">
            <p class="text-sm font-bold text-emerald-800">2FA activo</p>
            <p class="mt-1 text-xs text-emerald-700">Desde ahora se pedira un codigo al iniciar sesion en esta cuenta.</p>
        </div>

        <div class="flex flex-col gap-4 sm:flex-row">
            <form method="POST" action="{{ route('profile.two-factor.recovery-codes') }}" class="space-y-3">
                @csrf
                <input name="password" type="password" required autocomplete="current-password"
                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    placeholder="Contrasena actual">
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-white border border-slate-200 rounded-lg font-bold text-xs text-slate-700 uppercase tracking-widest hover:bg-slate-50 transition">
                    Regenerar codigos
                </button>
            </form>

            <form method="POST" action="{{ route('profile.two-factor.disable') }}" class="space-y-3">
                @csrf
                @method('DELETE')
                <input name="password" type="password" required autocomplete="current-password"
                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-rose-500 focus:ring-rose-500"
                    placeholder="Contrasena actual">
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-rose-600 border border-transparent rounded-lg font-bold text-xs text-white uppercase tracking-widest hover:bg-rose-700 transition">
                    Desactivar 2FA
                </button>
            </form>
        </div>
    @endif
</section>
