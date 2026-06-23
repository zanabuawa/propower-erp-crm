<div class="space-y-5">
    <style>
        .photo-page {
            position: relative;
            width: 816px;
            height: 1056px;
            box-sizing: border-box;
            overflow: hidden;
            background: #fff;
        }
        .page-break-label {
            position: absolute;
            left: 0;
            right: 0;
            height: 40px;
            border-top: 2px dashed #ef4444;
            background: #e2e8f0;
            color: #dc2626;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: .08em;
            text-align: right;
            text-transform: uppercase;
            pointer-events: none;
            z-index: 10;
        }
        .page-footer-marker {
            position: absolute;
            left: 40px;
            right: 40px;
            height: 70px;
            text-align: center;
            z-index: 7;
            pointer-events: none;
        }
        .page-footer-marker .page-number {
            margin-bottom: 6px;
            font-size: 10px;
            font-weight: 800;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: .08em;
        }
        .page-footer-marker .footer-logo {
            margin-top: 0;
            padding-top: 8px;
        }
        .page-break-label span {
            display: inline-block;
            margin-top: 10px;
            margin-right: 10px;
            padding: 2px 6px;
            background: #fff1f2;
            border: 1px solid #fecdd3;
            border-radius: 999px;
        }
        .repeated-page-header {
            position: absolute;
            left: 40px;
            right: 40px;
            z-index: 6;
            background: #fff;
            pointer-events: none;
        }
        .repeated-page-header .client-table { margin-bottom: 0; }
        .client-table { width: 100%; border-collapse: collapse; margin-bottom: 22px; table-layout: fixed; }
        .client-table td, .client-table th { border: 1px solid #0f172a; padding: 8px 10px; vertical-align: middle; }
        .client-logo-cell { width: 240px; text-align: center; }
        .client-logo { min-height: 140px; display: flex; align-items: center; justify-content: center; overflow: hidden; }
        .client-logo img { max-height: 130px; max-width: 215px; object-fit: contain; }
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
        .table-title { text-align: center; font-size: 16px; font-weight: 900; text-transform: uppercase; letter-spacing: .1em; background: #f1f5f9; }
        .table-label { width: 115px; font-size: 11px; font-weight: 900; text-transform: uppercase; letter-spacing: .08em; background: #f8fafc; color: #334155; }
        .table-value { font-size: 13px; font-weight: 700; color: #0f172a; overflow-wrap: anywhere; }
        .report-body { outline: none; font-size: 14px; line-height: 1.6; color: #334155; margin-bottom: 22px; }
        .report-body:empty::before { content: 'Descripcion opcional del reporte...'; color: #94a3b8; }
        .report-body h1 { margin: 22px 0 12px; font-size: 22px; line-height: 1.25; font-weight: 900; text-transform: uppercase; letter-spacing: .08em; color: #0f172a; }
        .report-body h2 { margin: 20px 0 12px; padding-bottom: 6px; border-bottom: 1px solid #e2e8f0; font-size: 17px; line-height: 1.3; font-weight: 900; text-transform: uppercase; letter-spacing: .12em; color: #1e293b; }
        .report-body h3 { margin: 16px 0 8px; font-size: 15px; font-weight: 800; color: #1e293b; }
        .report-body p { margin: 0 0 12px; }
        .photo-section-title {
            margin: 0 0 14px;
            padding-bottom: 6px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 13px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: .12em;
            color: #1e293b;
        }
        .photo-layout { display: flex; flex-wrap: wrap; align-items: flex-start; justify-content: center; gap: 12px; }
        .photo-card {
            position: relative;
            border: 0;
            background: transparent;
            padding: 0;
            break-inside: avoid;
            page-break-inside: avoid;
            cursor: grab;
        }
        .photo-card.dragging { opacity: .45; outline: 2px dashed #4f46e5; }
        .photo-card.is-hidden { opacity: .35; filter: grayscale(1); }
        .photo-card img { width: 100%; object-fit: contain; display: block; background: transparent; }
        .photo-controls {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
            margin-top: 8px;
            font-size: 10px;
            color: #64748b;
        }
        .photo-page-meta {
            width: 100%;
            margin-top: 6px;
            display: flex;
            justify-content: center;
            gap: 8px;
            align-items: center;
        }
        .footer-logo { margin-top: 34px; padding-top: 14px; border-top: 1px solid #e2e8f0; text-align: center; }
        .footer-logo img { max-height: 60px; max-width: 205px; object-fit: contain; display: inline-block; }
        .footer-placeholder { font-weight: 900; text-transform: uppercase; letter-spacing: .1em; color: #475569; }
        .preview-page-footer {
            position: absolute;
            left: 45px;
            right: 45px;
            bottom: 45px;
            height: 53px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-top: 1px solid #e5e7eb;
            text-align: center;
        }
        .preview-page-footer .footer-logo {
            position: absolute;
            left: 50%;
            top: 54%;
            transform: translate(-50%, -50%);
            margin-top: 0;
            padding-top: 0;
        }
        .preview-page-footer .footer-logo img { max-height: 42px; max-width: 170px; }
        .preview-page-footer .page-number {
            position: absolute;
            right: 0;
            top: 54%;
            transform: translateY(-50%);
            width: 110px;
            text-align: right;
        }
        .page-number {
            margin-bottom: 6px;
            font-size: 10px;
            font-weight: 800;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: .08em;
        }
        .preview-page {
            padding: 45px 45px 112px;
        }
    </style>

    <div class="flex flex-col gap-3 lg:flex-row lg:items-center">
        <a wire:navigate href="{{ route('works.projects.show', ['project' => $project->id, 'tab' => 'photos']) }}"
           class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-slate-700">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7"/></svg>
            Volver al proyecto
        </a>
        <div class="flex-1">
            <p class="text-xs font-semibold tracking-widest text-indigo-500 uppercase">Formato fotografico</p>
            <h1 class="text-xl font-semibold text-slate-900">Acomodo de evidencia fotografica</h1>
            <p class="text-sm text-slate-500">{{ $project->name }} &middot; {{ $report->report_date?->format('d/m/Y') }}</p>
        </div>
        <div class="flex gap-2">
            <button type="button" x-data @click="$dispatch('save-photo-document')"
                    class="px-3 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">
                Guardar acomodo
            </button>
            <button type="button" x-data @click="$dispatch('print-photo-document')"
                    class="px-3 py-2 text-sm font-medium text-indigo-700 bg-indigo-50 border border-indigo-100 rounded-lg hover:bg-indigo-100">
                Imprimir
            </button>
        </div>
    </div>

    <x-alert />

    <div class="grid gap-4 xl:grid-cols-[1fr_280px]"
         x-data="{
            body: @js($custom_body),
            photos: @js($photoItems),
            printUrl: @js(route('works.photo-reports.print', $report)),
            dragged: null,
            draggedPath: null,
            pageCount: 1,
            pageDates: {},
            autoPaginating: false,
            savedMessage: '',
            savedTimer: null,
            init() {
                this.normalizePages();
                this.$nextTick(() => {
                    if (this.$refs.body) {
                        this.$refs.body.innerHTML = this.body;
                    }
                    this.drawPageBreaks();
                });
                window.addEventListener('save-photo-document', () => this.save());
                window.addEventListener('print-photo-document', () => this.saveAndPrint());
                window.addEventListener('photo-report-photos-updated', (event) => {
                    this.photos = event.detail.photos;
                    this.normalizePages();
                    this.$nextTick(() => this.drawPageBreaks());
                });
                window.addEventListener('resize', () => this.drawPageBreaks());
            },
            visiblePhotos() {
                return this.photos.filter((photo) => !photo.hidden);
            },
            start(index, event = null) {
                this.dragged = index;
                this.draggedPath = this.photos[index]?.path || null;
                if (event?.dataTransfer && this.draggedPath) {
                    event.dataTransfer.effectAllowed = 'move';
                    event.dataTransfer.setData('text/plain', this.draggedPath);
                }
            },
            endDrag() {
                setTimeout(() => {
                    this.dragged = null;
                    this.draggedPath = null;
                }, 0);
            },
            draggedPhotoPath(event = null) {
                return this.draggedPath || event?.dataTransfer?.getData('text/plain') || null;
            },
            drop(index, event = null) {
                this.movePhotoBefore(this.draggedPhotoPath(event), this.photos[index]?.path);
            },
            normalizePages() {
                this.photos.forEach((photo) => {
                    photo.page = Math.max(1, Number(photo.page || 1));
                    photo.manual_page = Boolean(photo.manual_page);
                });
                this.pageCount = Math.max(1, this.pageCount || 1, ...this.photos.map((photo) => Number(photo.page || 1)));
                this.ensurePageDates();
            },
            photoPages() {
                const pages = Array.from({ length: this.pageCount }, (_, index) => ({
                    number: index + 1,
                    startIndex: 0,
                    photos: [],
                }));

                this.photos.forEach((photo, index) => {
                    const pageIndex = Math.max(0, Math.min(this.pageCount - 1, Number(photo.page || 1) - 1));
                    pages[pageIndex].photos.push({ photo, index });
                });

                return pages;
            },
            isPageStart(index) {
                const photo = this.photos[index];
                if (!photo) return false;
                return this.photos.findIndex((item) => Number(item.page || 1) === Number(photo.page || 1)) === index;
            },
            setPageDate(pageIndex, date) {
                const pageNumber = pageIndex + 1;
                this.pageDates[pageNumber] = date || this.defaultPageDate();
                this.pageDates = { ...this.pageDates };
                this.photos.forEach((photo) => {
                    if (Number(photo.page || 1) === pageNumber) {
                        photo.page_date = this.pageDates[pageNumber];
                    }
                });
                this.refreshPhotos();
                this.$nextTick(() => this.drawPageBreaks());
            },
            addPage() {
                this.pageCount += 1;
                this.$nextTick(() => this.drawPageBreaks());
            },
            rebuildFromPages(pages) {
                this.pageCount = Math.max(1, pages.length);
                this.photos = pages
                    .flatMap((page, pageIndex) => page.photos.map((photo) => ({
                        ...photo,
                        page: pageIndex + 1,
                        break_after: false,
                    })));
                this.ensurePageDates();
                this.$nextTick(() => this.drawPageBreaks());
            },
            ensurePageDates() {
                this.photoPages().forEach((page, pageIndex) => {
                    const pageNumber = pageIndex + 1;
                    const firstDate = page.photos.find((item) => item.photo.page_date)?.photo.page_date;
                    this.pageDates[pageNumber] = this.pageDates[pageNumber] || firstDate || this.defaultPageDate();
                    page.photos.forEach((item) => {
                        item.photo.page_date = this.pageDates[pageNumber];
                    });
                });
                this.pageDates = { ...this.pageDates };
            },
            refreshPhotos() {
                this.photos = this.photos.map((photo) => ({ ...photo }));
                this.normalizePages();
                this.$nextTick(() => this.drawPageBreaks());
            },
            movePhotoBefore(path, targetPath) {
                if (!path || !targetPath || path === targetPath) return;
                const moved = this.photos.find((photo) => photo.path === path);
                const target = this.photos.find((photo) => photo.path === targetPath);
                if (!moved || !target) return;

                moved.page = Number(target.page || 1);
                moved.manual_page = true;
                const from = this.photos.findIndex((photo) => photo.path === path);
                const to = this.photos.findIndex((photo) => photo.path === targetPath);
                if (from >= 0 && to >= 0) {
                    const [photo] = this.photos.splice(from, 1);
                    const insertAt = from < to ? to - 1 : to;
                    this.photos.splice(insertAt, 0, photo);
                }

                this.dragged = null;
                this.draggedPath = null;
                this.refreshPhotos();
            },
            movePhotoAfter(path, targetPath) {
                if (!path || !targetPath || path === targetPath) return;
                const moved = this.photos.find((photo) => photo.path === path);
                const target = this.photos.find((photo) => photo.path === targetPath);
                if (!moved || !target) return;

                moved.page = Number(target.page || 1);
                moved.manual_page = true;
                const from = this.photos.findIndex((photo) => photo.path === path);
                const to = this.photos.findIndex((photo) => photo.path === targetPath);
                if (from >= 0 && to >= 0) {
                    const [photo] = this.photos.splice(from, 1);
                    const insertAt = from < to ? to : to + 1;
                    this.photos.splice(insertAt, 0, photo);
                }

                this.dragged = null;
                this.draggedPath = null;
                this.refreshPhotos();
            },
            movePhoto(index, delta) {
                const target = index + delta;
                if (target < 0 || target >= this.photos.length) return;
                if (delta < 0) {
                    this.movePhotoBefore(this.photos[index]?.path, this.photos[target]?.path);
                } else {
                    this.movePhotoAfter(this.photos[index]?.path, this.photos[target]?.path);
                }
            },
            movePhotoToPage(index, targetPageIndex) {
                this.movePhotoPathToPage(this.photos[index]?.path, targetPageIndex);
            },
            movePhotoPathToPage(path, targetPageIndex) {
                const photo = this.photos.find((item) => item.path === path);
                if (!photo || targetPageIndex < 0) return;

                this.pageCount = Math.max(this.pageCount, targetPageIndex + 1);
                photo.page = targetPageIndex + 1;
                photo.manual_page = true;
                photo.page_date = this.dateForPage(targetPageIndex);
                const currentIndex = this.photos.findIndex((item) => item.path === path);
                if (currentIndex >= 0) {
                    const [moved] = this.photos.splice(currentIndex, 1);
                    let insertAt = this.photos.length;
                    for (let i = this.photos.length - 1; i >= 0; i--) {
                        if (Number(this.photos[i].page || 1) === targetPageIndex + 1) {
                            insertAt = i + 1;
                            break;
                        }
                    }
                    this.photos.splice(insertAt, 0, moved);
                }
                this.dragged = null;
                this.draggedPath = null;
                this.refreshPhotos();
            },
            dropToPage(pageIndex, event = null) {
                const path = this.draggedPhotoPath(event);
                if (!path) return;
                this.movePhotoPathToPage(path, pageIndex);
            },
            dropToPageBlank(pageIndex, event = null) {
                if (event?.target?.closest?.('.photo-card')) return;
                this.dropToPage(pageIndex, event);
            },
            defaultPageDate() {
                return @js($report->report_date?->format('Y-m-d'));
            },
            dateForPage(pageIndex) {
                return this.pageDates[pageIndex + 1] || this.defaultPageDate();
            },
            pageDateLabel(date) {
                if (!date) return '{{ $report->report_date?->format('d/m/Y') }}';
                const parts = date.split('-');
                return parts.length === 3 ? parts[2] + '/' + parts[1] + '/' + parts[0] : date;
            },
            clamp(photo) {
                photo.scale = Math.max(25, Math.min(100, Number(photo.scale || photo.width || 50)));
                this.$nextTick(() => this.drawPageBreaks());
            },
            resize(photo, delta) {
                photo.scale = Math.max(25, Math.min(100, Number(photo.scale || 50) + delta));
                this.$nextTick(() => this.drawPageBreaks());
            },
            async remove(index) {
                const photo = this.photos[index];
                if (!confirm('Eliminar esta foto del reporte?')) return;
                await this.$wire.deletePhoto(photo.path);
                this.photos.splice(index, 1);
                this.$nextTick(() => this.drawPageBreaks());
            },
            syncBody() {
                if (this.$refs.body) {
                    this.body = this.$refs.body.innerHTML;
                }
            },
            async save() {
                this.syncBody();
                this.syncPageBreaks();
                await this.$wire.saveDocument(this.body, this.photos.map((photo) => ({
                    path: photo.path,
                    scale: Number(photo.scale),
                    page: Number(photo.page || 1),
                    manual_page: Boolean(photo.manual_page),
                    break_after: Boolean(photo.break_after),
                    page_date: this.dateForPage(Number(photo.page || 1) - 1),
                    hidden: false,
                })));
                this.showSavedMessage('Cambios guardados correctamente.');
            },
            showSavedMessage(message) {
                this.savedMessage = message;
                if (this.savedTimer) {
                    clearTimeout(this.savedTimer);
                }
                this.savedTimer = setTimeout(() => {
                    this.savedMessage = '';
                    this.savedTimer = null;
                }, 5000);
            },
            syncPageBreaks() {
                this.photos.forEach((photo, index) => {
                    const next = this.photos[index + 1];
                    photo.break_after = Boolean(next && Number(next.page || 1) > Number(photo.page || 1));
                });
            },
            async saveAndPrint() {
                await this.save();
                window.open(this.printUrl, '_blank');
            },
            drawPageBreaks() {
                this.ensurePageDates();
                this.$nextTick(() => this.paginateOverflow());
            },
            paginateOverflow() {
                if (this.autoPaginating) return;
                this.autoPaginating = true;

                let changed = false;
                const footerReserve = 112;
                const pages = Array.from(this.$el.querySelectorAll('.preview-page'));

                pages.forEach((pageEl, pageIndex) => {
                    const cards = Array.from(pageEl.querySelectorAll('.page-flow-item'));
                    const limit = pageEl.clientHeight - footerReserve;
                    const overflowIndex = cards.findIndex((card, cardIndex) => {
                        if (cardIndex === 0) return false;
                        return card.offsetTop + card.offsetHeight > limit;
                    });

                    if (overflowIndex === -1) return;

                    this.pageCount = Math.max(this.pageCount, pageIndex + 2);
                    cards.slice(overflowIndex).forEach((card) => {
                        const photo = this.photos.find((item) => item.path === card.dataset.path);
                        if (photo && !photo.manual_page && Number(photo.page || 1) === pageIndex + 1) {
                            photo.page = pageIndex + 2;
                            changed = true;
                        }
                    });
                });

                this.autoPaginating = false;
                if (changed) {
                    this.ensurePageDates();
                    this.$nextTick(() => this.paginateOverflow());
                }
            }
         }">
        <div x-show="savedMessage"
             x-transition.opacity.duration.200ms
             x-cloak
             class="fixed z-50 px-4 py-3 text-sm font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-lg shadow-sm top-5 right-5"
             x-text="savedMessage">
        </div>

        <div class="bg-slate-100 border border-slate-200 rounded-xl p-4 overflow-x-auto">
            @php
                $companyLogo = $company?->print_logo ?? $company?->logo;
                $customerLogo = $customer?->image;
                $place = $report->location
                    ?: collect([$customer?->address, $customer?->city, $customer?->state])->filter()->join(', ');
                $weekStart = $report->week_start ?: $report->report_date?->copy()->startOfWeek();
                $weekEnd = $report->week_end ?: $report->report_date?->copy()->endOfWeek();
                $weekLabel = $weekStart && $weekEnd
                    ? $weekStart->format('d/m/Y') . ' - ' . $weekEnd->format('d/m/Y')
                    : 'N/A';
            @endphp

            <div class="space-y-8">
                <template x-for="(page, pageIndex) in photoPages()" :key="'preview-page-' + page.number">
                    <div class="photo-page preview-page mx-auto shadow-sm border border-slate-200"
                         @dragover.prevent
                         @drop.prevent="dropToPageBlank(pageIndex, $event)">
                        <table class="client-table">
                            <tr>
                                <td class="client-logo-cell" rowspan="6">
                                    <div class="client-logo">
                                        @if($customerLogo)
                                            <img src="{{ asset('storage/' . $customerLogo) }}" alt="{{ $customer?->name }}">
                                        @else
                                            <div class="report-logo-placeholder">{{ mb_strtoupper(mb_substr($customer?->name ?? 'C', 0, 1)) }}</div>
                                        @endif
                                    </div>
                                </td>
                                <th class="table-title" colspan="2">Reporte fotografico</th>
                            </tr>
                            <tr><td class="table-label">Cliente</td><td class="table-value">{{ $customer?->name ?? 'Sin cliente' }}</td></tr>
                            <tr><td class="table-label">Lugar</td><td class="table-value">{{ $place ?: 'N/A' }}</td></tr>
                            <tr><td class="table-label">Fecha</td><td class="table-value" x-text="pageDateLabel(dateForPage(pageIndex))"></td></tr>
                            <tr><td class="table-label">Obra</td><td class="table-value">{{ $project->name }}</td></tr>
                            <tr><td class="table-label">Concepto</td><td class="table-value">{{ $report->title }}</td></tr>
                        </table>

                        <template x-if="pageIndex === 0">
                            <div>
                                <div x-ref="body"
                                     contenteditable="true"
                                     @input="syncBody(); drawPageBreaks()"
                                     class="report-body">
                                </div>

                                <h3 class="photo-section-title">Evidencia fotografica</h3>
                            </div>
                        </template>

                        <div class="photo-layout min-h-40"
                             @dragover.prevent
                             @drop.prevent="dropToPageBlank(pageIndex, $event)">
                            <template x-for="item in page.photos" :key="item.photo.path">
                                <div x-show="!item.photo.hidden"
                                     draggable="true"
                                     @dragstart="start(item.index, $event); $el.classList.add('dragging')"
                                     @dragend="$el.classList.remove('dragging'); endDrag()"
                                     @dragover.prevent
                                     @drop.prevent.stop="movePhotoBefore(draggedPhotoPath($event), item.photo.path)"
                                     class="photo-card page-flow-item"
                                     :data-path="item.photo.path"
                                     :data-page="item.photo.page || 1"
                                     :style="'width: calc(' + item.photo.scale + '% - 12px)'">
                                    <img :src="item.photo.url" alt="Evidencia fotografica" :style="'height: ' + Math.round(560 * (item.photo.scale / 100)) + 'px'">
                                </div>
                            </template>

                            <p x-show="!page.photos.length" class="w-full py-10 text-xs font-semibold text-center text-slate-400 border border-dashed border-slate-200 rounded-lg">
                                Pagina vacia. Arrastra una foto aqui.
                            </p>
                        </div>

                        <div class="preview-page-footer">
                            <div class="page-number" x-text="'Pagina ' + (pageIndex + 1)"></div>
                            <div class="footer-logo">
                                @if($companyLogo)
                                    <img src="{{ asset('storage/' . $companyLogo) }}" alt="{{ $company?->name }}">
                                @else
                                    <span class="footer-placeholder">{{ $company?->name ?? 'ProPower' }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <div class="hidden">
            <div x-ref="page" class="photo-page mx-auto shadow-sm border border-slate-200 p-10">
                <div x-ref="breaks"></div>

                @php
                    $companyLogo = $company?->print_logo ?? $company?->logo;
                    $customerLogo = $customer?->image;
                    $place = $report->location
                        ?: collect([$customer?->address, $customer?->city, $customer?->state])->filter()->join(', ');
                    $weekStart = $report->week_start ?: $report->report_date?->copy()->startOfWeek();
                    $weekEnd = $report->week_end ?: $report->report_date?->copy()->endOfWeek();
                    $weekLabel = $weekStart && $weekEnd
                        ? $weekStart->format('d/m/Y') . ' - ' . $weekEnd->format('d/m/Y')
                        : 'N/A';
                @endphp

                <table x-ref="headerTable" class="client-table">
                    <tr>
                        <td class="client-logo-cell" rowspan="6">
                            <div class="client-logo">
                                @if($customerLogo)
                                    <img src="{{ asset('storage/' . $customerLogo) }}" alt="{{ $customer?->name }}">
                                @else
                                    <div class="report-logo-placeholder">{{ mb_strtoupper(mb_substr($customer?->name ?? 'C', 0, 1)) }}</div>
                                @endif
                            </div>
                        </td>
                        <th class="table-title" colspan="2">Reporte fotografico</th>
                    </tr>
                    <tr><td class="table-label">Cliente</td><td class="table-value">{{ $customer?->name ?? 'Sin cliente' }}</td></tr>
                    <tr><td class="table-label">Lugar</td><td class="table-value">{{ $place ?: 'N/A' }}</td></tr>
                    <tr><td class="table-label">Fecha</td><td class="table-value" data-header-date>{{ $report->report_date?->format('d/m/Y') }}</td></tr>
                    <tr><td class="table-label">Obra</td><td class="table-value">{{ $project->name }}</td></tr>
                    <tr><td class="table-label">Concepto</td><td class="table-value">{{ $report->title }}</td></tr>
                </table>

                <div x-ref="legacyBody"
                     contenteditable="true"
                     @input="syncBody(); drawPageBreaks()"
                     class="report-body">
                </div>

                <h3 class="photo-section-title">Evidencia fotografica</h3>
                <div class="photo-layout">
                    <template x-for="(photo, index) in photos" :key="photo.path">
                        <div x-show="!photo.hidden"
                             draggable="true"
                             @dragstart="start(index, $event); $el.classList.add('dragging')"
                             @dragend="$el.classList.remove('dragging'); endDrag()"
                             @dragover.prevent
                             @drop.prevent="drop(index, $event)"
                             class="photo-card page-flow-item"
                             :data-path="photo.path"
                             :data-page="photo.page || 1"
                             :data-page-date="photo.page_date || defaultPageDate()"
                             :data-break-after="photo.break_after ? 'true' : 'false'"
                             :style="'width: calc(' + photo.scale + '% - 12px)'">
                            <img :src="photo.url" alt="Evidencia fotografica" :style="'height: ' + Math.round(560 * (photo.scale / 100)) + 'px'">
                            <div class="photo-controls">
                                <button type="button" @click="resize(photo, -5)" class="flex items-center justify-center w-7 h-7 text-sm font-bold text-slate-600 bg-white border border-slate-200 rounded-full hover:bg-slate-50">-</button>
                                <span class="min-w-10 text-center text-[10px] font-bold text-slate-500" x-text="photo.scale + '%'"></span>
                                <button type="button" @click="resize(photo, 5)" class="flex items-center justify-center w-7 h-7 text-sm font-bold text-slate-600 bg-white border border-slate-200 rounded-full hover:bg-slate-50">+</button>
                                <label class="hidden">
                                    Tamaño
                                    <input type="range" min="25" max="100" step="5" x-model.number="photo.scale" @input="clamp(photo)" class="w-full">
                                </label>
                                <button type="button" @click="remove(index)" class="px-2 py-1 text-[10px] font-bold text-red-600 bg-red-50 rounded-md hover:bg-red-100">Eliminar</button>
                                <div class="photo-page-meta" x-show="isPageStart(index)">
                                    <span class="text-[10px] font-bold text-slate-500">Fecha pagina</span>
                                    <input type="date" x-model="photo.page_date" @input="drawPageBreaks()" class="px-2 py-1 text-[10px] border border-slate-200 rounded-md">
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <div x-ref="footerTemplate" class="hidden">
                    <div class="footer-logo">
                        @if($companyLogo)
                            <img src="{{ asset('storage/' . $companyLogo) }}" alt="{{ $company?->name }}">
                        @else
                            <span class="footer-placeholder">{{ $company?->name ?? 'ProPower' }}</span>
                        @endif
                    </div>
                </div>

            </div>
        </div>
        </div>

        <aside class="space-y-3">
            <div class="p-4 bg-white border border-slate-200 rounded-xl">
                <div class="flex items-center justify-between gap-2">
                    <h2 class="text-sm font-semibold text-slate-800">Organizar paginas</h2>
                    <button type="button" @click="addPage()" class="px-2 py-1 text-[10px] font-bold text-white bg-indigo-600 rounded-md hover:bg-indigo-700">
                        Agregar nueva pagina
                    </button>
                </div>
                <p class="mt-1 text-xs text-slate-400">Mueve cada foto a la pagina que corresponda y ajusta la fecha de cada pagina.</p>
                <div class="mt-3 space-y-3">
                    <template x-for="(page, pageIndex) in photoPages()" :key="'page-menu-' + page.number">
                        <div class="p-3 border border-slate-200 rounded-lg"
                             @dragover.prevent
                             @drop.prevent="dropToPage(pageIndex, $event)">
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-xs font-bold text-slate-700" x-text="'Pagina ' + (pageIndex + 1)"></span>
                                <input type="date" :value="dateForPage(pageIndex)" @input="setPageDate(pageIndex, $event.target.value)" class="w-32 px-2 py-1 text-[10px] border border-slate-200 rounded-md">
                            </div>
                            <div class="mt-2 space-y-2">
                                <template x-for="item in page.photos" :key="'menu-' + item.photo.path">
                                    <div class="flex items-center gap-2 p-1 rounded-md hover:bg-slate-50"
                                         draggable="true"
                                         @dragstart="start(item.index, $event); $el.classList.add('opacity-50')"
                                         @dragend="$el.classList.remove('opacity-50'); endDrag()"
                                         @dragover.prevent
                                         @drop.prevent.stop="movePhotoBefore(draggedPhotoPath($event), item.photo.path)">
                                        <img :src="item.photo.url" alt="" class="object-cover w-10 h-10 rounded-md">
                                        <div class="flex-1 min-w-0">
                                            <p class="text-[10px] font-semibold text-slate-500 truncate" x-text="'Foto ' + (item.index + 1)"></p>
                                            <div class="flex flex-wrap gap-1 mt-1">
                                                <button type="button" @click="movePhoto(item.index, -1)" class="px-1.5 py-0.5 text-[10px] font-bold bg-slate-100 rounded">Subir</button>
                                                <button type="button" @click="movePhoto(item.index, 1)" class="px-1.5 py-0.5 text-[10px] font-bold bg-slate-100 rounded">Bajar</button>
                                                <button type="button" @click="resize(item.photo, -5)" class="px-1.5 py-0.5 text-[10px] font-bold bg-slate-100 rounded">-</button>
                                                <span class="px-1.5 py-0.5 text-[10px] font-bold text-slate-500" x-text="item.photo.scale + '%'"></span>
                                                <button type="button" @click="resize(item.photo, 5)" class="px-1.5 py-0.5 text-[10px] font-bold bg-slate-100 rounded">+</button>
                                                <select :value="pageIndex" @change="movePhotoToPage(item.index, Number($event.target.value))" class="px-1.5 py-0.5 text-[10px] font-bold bg-white border border-slate-200 rounded">
                                                    <template x-for="(_, targetPageIndex) in photoPages()" :key="'target-' + item.photo.path + '-' + targetPageIndex">
                                                        <option :value="targetPageIndex" x-text="'A pagina ' + (targetPageIndex + 1)"></option>
                                                    </template>
                                                </select>
                                                <button type="button" @click="remove(item.index)" class="px-1.5 py-0.5 text-[10px] font-bold text-red-600 bg-red-50 rounded">Eliminar</button>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                                <p x-show="!page.photos.length" class="py-2 text-[10px] font-semibold text-center text-slate-400 bg-slate-50 rounded-md">
                                    Pagina vacia. Mueve una foto aqui.
                                </p>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <div class="p-4 bg-white border border-slate-200 rounded-xl">
                <h2 class="text-sm font-semibold text-slate-800">Agregar fotos</h2>
                <p class="mt-1 text-xs text-slate-400">Se agregan al final del reporte sin reemplazar las actuales.</p>
                <div class="mt-3 space-y-3">
                    <input wire:model="newPhotos" type="file" multiple accept="image/*"
                           class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-indigo-50 file:text-indigo-600 file:text-xs file:font-bold">
                    @error('newPhotos') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                    @error('newPhotos.*') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                    <button type="button" wire:click="addPhotos" wire:loading.attr="disabled" wire:target="newPhotos,addPhotos"
                            class="w-full px-3 py-2 text-xs font-bold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 disabled:opacity-60">
                        <span wire:loading.remove wire:target="addPhotos">Agregar al reporte</span>
                        <span wire:loading wire:target="addPhotos">Agregando...</span>
                    </button>
                </div>
            </div>
            <div class="p-4 bg-white border border-slate-200 rounded-xl">
                <h2 class="text-sm font-semibold text-slate-800">Guia de acomodo</h2>
                <p class="mt-2 text-xs leading-5 text-slate-500">Arrastra las fotos para cambiar el orden dentro del lienzo. En el panel de paginas puedes crear paginas nuevas y mandar fotos a cada pagina.</p>
            </div>
        </aside>
    </div>
</div>
