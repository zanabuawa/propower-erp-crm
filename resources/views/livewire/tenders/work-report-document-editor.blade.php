<div class="space-y-5">
    <style>
        .report-header {
            display: grid;
            grid-template-columns: 220px minmax(0, 1fr) 220px;
            align-items: center;
            gap: 24px;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 20px;
            margin-bottom: 28px;
        }
        .report-logo-side { text-align: center; min-width: 0; }
        .report-logo-box {
            height: 128px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        .report-logo-box img { max-height: 122px; max-width: 210px; object-fit: contain; }
        .report-logo-placeholder {
            width: 104px;
            height: 104px;
            border-radius: 10px;
            background: #eef2ff;
            color: #4f46e5;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            font-weight: 900;
        }
        .report-role {
            display: inline-block;
            margin-top: 6px;
            padding: 4px 10px;
            border-radius: 999px;
            background: #eef2ff;
            color: #4338ca;
            font-size: 10px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: .12em;
        }
        .report-title { text-align: center; min-width: 0; }
        .report-title h2 {
            margin: 0;
            font-size: 18px;
            line-height: 1.2;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: .14em;
            color: #0f172a;
        }
        .report-title p { margin: 8px 0 0; font-size: 14px; line-height: 1.35; color: #475569; overflow-wrap: anywhere; }
        .report-editor { min-height: 680px; outline: none; font-size: 14px; line-height: 1.6; color: #334155; }
        .report-editor:empty::before { content: 'Escribe aqui el contenido del reporte...'; color: #94a3b8; }
        .report-editor h1 { margin: 22px 0 12px; font-size: 22px; line-height: 1.25; font-weight: 900; text-transform: uppercase; letter-spacing: .08em; color: #0f172a; }
        .report-editor h2 { margin: 20px 0 12px; padding-bottom: 6px; border-bottom: 1px solid #e2e8f0; font-size: 17px; line-height: 1.3; font-weight: 900; text-transform: uppercase; letter-spacing: .12em; color: #1e293b; }
        .report-editor h3 { margin: 16px 0 8px; font-size: 15px; font-weight: 800; color: #1e293b; }
        .report-editor p { margin: 0 0 12px; }
        .report-editor ul { margin: 0 0 14px 0; padding-left: 28px; list-style: disc; }
        .report-editor ol { margin: 0 0 14px 0; padding-left: 28px; list-style: decimal; }
        .report-editor li { margin: 0 0 6px; padding-left: 4px; display: list-item; }
        .report-editor blockquote { margin: 14px 0; padding-left: 16px; border-left: 4px solid #c7d2fe; color: #475569; font-style: italic; }
    </style>

    <div class="flex flex-col gap-3 lg:flex-row lg:items-center">
        <a wire:navigate href="{{ route('works.projects.show', ['project' => $project->id, 'tab' => 'weekly']) }}"
           class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-slate-700">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7"/></svg>
            Volver al proyecto
        </a>
        <div class="flex-1">
            <p class="text-xs font-semibold tracking-widest text-indigo-500 uppercase">Formato libre</p>
            <h1 class="text-xl font-semibold text-slate-900">Reporte semanal tipo documento</h1>
            <p class="text-sm text-slate-500">{{ $project->name }} · {{ $report->week_start?->format('d/m/Y') }} - {{ $report->week_end?->format('d/m/Y') }}</p>
        </div>
        <div class="flex gap-2">
            <button type="button" wire:click="resetToDefault"
                    class="px-3 py-2 text-sm font-medium text-slate-600 bg-white border border-slate-200 rounded-lg hover:bg-slate-50">
                Restaurar base
            </button>
            <button type="button" x-data @click="$dispatch('save-free-report')"
                    class="px-3 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">
                Guardar formato
            </button>
            <a href="{{ route('works.reports.print', $report) }}" target="_blank"
               class="px-3 py-2 text-sm font-medium text-indigo-700 bg-indigo-50 border border-indigo-100 rounded-lg hover:bg-indigo-100">
                Imprimir
            </a>
        </div>
    </div>

    <x-alert />

    <div class="bg-slate-100 border border-slate-200 rounded-xl p-4 overflow-x-auto"
         x-data="{
            body: @js($custom_body),
            active: { bold: false, italic: false, underline: false, ul: false, ol: false, h2: false },
            init() {
                this.$nextTick(() => {
                    this.$refs.editor.innerHTML = this.body;
                    this.refreshState();
                });
                window.addEventListener('save-free-report', () => this.save());
                window.addEventListener('free-report-reset', (event) => {
                    this.body = event.detail.body;
                    this.$refs.editor.innerHTML = this.body;
                    this.refreshState();
                });
                document.addEventListener('selectionchange', () => {
                    if (this.$refs.editor && this.$refs.editor.contains(window.getSelection()?.anchorNode)) {
                        this.refreshState();
                    }
                });
            },
            focusEditor() {
                this.$refs.editor.focus();
            },
            command(cmd) {
                this.focusEditor();
                document.execCommand(cmd, false, null);
                this.sync();
                this.refreshState();
            },
            block(tag) {
                this.focusEditor();
                document.execCommand('formatBlock', false, '<' + tag + '>');
                this.sync();
                this.refreshState();
            },
            list(type) {
                this.focusEditor();
                document.execCommand(type, false, null);
                this.sync();
                this.refreshState();
            },
            sync() {
                this.body = this.$refs.editor.innerHTML;
            },
            refreshState() {
                this.active.bold = document.queryCommandState('bold');
                this.active.italic = document.queryCommandState('italic');
                this.active.underline = document.queryCommandState('underline');
                this.active.ul = document.queryCommandState('insertUnorderedList');
                this.active.ol = document.queryCommandState('insertOrderedList');
                const node = window.getSelection()?.anchorNode;
                const element = node?.nodeType === Node.TEXT_NODE ? node.parentElement : node;
                this.active.h2 = Boolean(element?.closest?.('h2'));
            },
            save() {
                this.sync();
                this.$wire.set('custom_body', this.body);
                this.$wire.save();
            }
         }">
        <div class="sticky top-0 left-0 z-20 mb-4 bg-slate-100/95 backdrop-blur border-b border-slate-200">
            <div class="mx-auto w-[900px] max-w-none flex flex-wrap items-center justify-center gap-1 p-3">
                <button type="button" @click="command('bold')" :class="active.bold ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-slate-700 border-slate-200'" class="px-3 py-1.5 text-xs font-bold border rounded-lg">B</button>
                <button type="button" @click="command('italic')" :class="active.italic ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-slate-700 border-slate-200'" class="px-3 py-1.5 text-xs italic border rounded-lg">I</button>
                <button type="button" @click="command('underline')" :class="active.underline ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-slate-700 border-slate-200'" class="px-3 py-1.5 text-xs underline border rounded-lg">U</button>
                <button type="button" @click="list('insertUnorderedList')" :class="active.ul ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-slate-700 border-slate-200'" class="px-3 py-1.5 text-xs border rounded-lg">Lista</button>
                <button type="button" @click="list('insertOrderedList')" :class="active.ol ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-slate-700 border-slate-200'" class="px-3 py-1.5 text-xs border rounded-lg">1. Lista</button>
                <button type="button" @click="block('h2')" :class="active.h2 ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-slate-700 border-slate-200'" class="px-3 py-1.5 text-xs border rounded-lg">Titulo</button>
            </div>
        </div>

        <div class="mx-auto bg-white shadow-sm border border-slate-200 w-[900px] max-w-none min-h-[1050px] p-10">
            @php
                $companyLogo = $company?->print_logo ?? $company?->logo;
                $customerLogo = $customer?->image;
            @endphp

            <div class="report-header">
                <div class="report-logo-side">
                    <div class="report-logo-box">
                        @if($companyLogo)
                            <img src="{{ asset('storage/' . $companyLogo) }}" alt="{{ $company?->name }}">
                        @else
                            <div class="report-logo-placeholder">{{ mb_strtoupper(mb_substr($company?->name ?? 'E', 0, 1)) }}</div>
                        @endif
                    </div>
                    <span class="report-role">Prestador</span>
                </div>
                <div class="report-title">
                    <h2>Reporte semanal de obra</h2>
                    <p><strong>Proyecto:</strong> {{ $project->name }}</p>
                    <p><strong>Fecha:</strong> {{ $report->week_start?->format('d/m/Y') }} - {{ $report->week_end?->format('d/m/Y') }}</p>
                </div>
                <div class="report-logo-side">
                    <div class="report-logo-box">
                        @if($customerLogo)
                            <img src="{{ asset('storage/' . $customerLogo) }}" alt="{{ $customer?->name }}">
                        @else
                            <div class="report-logo-placeholder">{{ mb_strtoupper(mb_substr($customer?->name ?? 'C', 0, 1)) }}</div>
                        @endif
                    </div>
                    <span class="report-role">Cliente</span>
                </div>
            </div>

            <div x-ref="editor"
                 contenteditable="true"
                 @input="sync"
                 @keyup="refreshState"
                 @mouseup="refreshState"
                 class="report-editor">
            </div>
            @error('custom_body') <p class="mt-2 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>
    </div>
</div>
