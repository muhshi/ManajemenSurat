<div x-data="{ activeTab: 'sop', activeFaq: 1 }" class="pedoman-modal-wrapper">
    <style>
        .pedoman-modal-wrapper {
            font-family: inherit;
            color: #1e293b;
        }
        .dark .pedoman-modal-wrapper {
            color: #e2e8f0;
        }

        /* Hero Banner */
        .pedoman-hero {
            background: linear-gradient(135deg, #0054A6 0%, #00366D 100%);
            border-radius: 1rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 10px 25px -5px rgba(0, 84, 166, 0.25), 0 8px 10px -6px rgba(0, 84, 166, 0.2);
            color: #ffffff;
            display: flex;
            flex-direction: column;
            gap: 1rem;
            position: relative;
            overflow: hidden;
        }
        @media (min-width: 768px) {
            .pedoman-hero {
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
                padding: 1.5rem 1.75rem;
            }
        }
        .pedoman-hero::after {
            content: '';
            position: absolute;
            right: -30px;
            top: -30px;
            width: 140px;
            height: 140px;
            background: radial-gradient(circle, rgba(255,255,255,0.12) 0%, rgba(255,255,255,0) 70%);
            border-radius: 50%;
            pointer-events: none;
        }
        .pedoman-hero-content {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            position: relative;
            z-index: 1;
        }
        .pedoman-hero-icon {
            width: 44px;
            height: 44px;
            min-width: 44px;
            border-radius: 0.75rem;
            background: rgba(255, 255, 255, 0.16);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.25);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
        }
        .pedoman-hero-icon svg {
            width: 24px;
            height: 24px;
        }
        .pedoman-hero-title {
            font-size: 1.125rem;
            font-weight: 700;
            line-height: 1.35;
            margin: 0 0 0.25rem 0;
            letter-spacing: -0.01em;
            color: #ffffff;
        }
        .pedoman-hero-desc {
            font-size: 0.84rem;
            color: rgba(255, 255, 255, 0.88);
            margin: 0;
            line-height: 1.45;
        }
        .pedoman-download-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: #ffffff;
            color: #0054A6;
            font-weight: 600;
            font-size: 0.85rem;
            padding: 0.65rem 1.15rem;
            border-radius: 0.625rem;
            text-decoration: none;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.15);
            transition: all 0.2s ease;
            white-space: nowrap;
            position: relative;
            z-index: 1;
        }
        .pedoman-download-btn:hover {
            background: #f0f7ff;
            color: #00366D;
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.2);
        }
        .pedoman-download-btn svg {
            width: 18px;
            height: 18px;
            color: #0054A6;
        }

        /* Modern Segmented Navigation Tabs */
        .pedoman-tabs {
            display: flex;
            gap: 0.35rem;
            background: #f1f5f9;
            padding: 0.35rem;
            border-radius: 0.85rem;
            margin-bottom: 1.5rem;
            overflow-x: auto;
            border: 1px solid #e2e8f0;
        }
        .dark .pedoman-tabs {
            background: rgba(255, 255, 255, 0.04);
            border-color: rgba(255, 255, 255, 0.08);
        }
        .pedoman-tab-btn {
            flex: 1;
            min-width: max-content;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.6rem 1rem;
            font-size: 0.84rem;
            font-weight: 500;
            border-radius: 0.6rem;
            border: 1px solid transparent;
            cursor: pointer;
            transition: all 0.18s ease;
            color: #64748b;
            background: transparent;
        }
        .dark .pedoman-tab-btn {
            color: #94a3b8;
        }
        .pedoman-tab-btn:hover {
            color: #0f172a;
            background: rgba(255, 255, 255, 0.6);
        }
        .dark .pedoman-tab-btn:hover {
            color: #f1f5f9;
            background: rgba(255, 255, 255, 0.06);
        }
        .pedoman-tab-btn.is-active {
            background: #ffffff;
            color: #0054A6;
            font-weight: 600;
            border-color: #e2e8f0;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
        }
        .dark .pedoman-tab-btn.is-active {
            background: #1e293b;
            color: #38bdf8;
            border-color: rgba(255, 255, 255, 0.12);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.25);
        }
        .pedoman-tab-btn svg {
            width: 17px;
            height: 17px;
            flex-shrink: 0;
        }

        /* SOP Steps Grid */
        .pedoman-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
        }
        @media (min-width: 768px) {
            .pedoman-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        .pedoman-step-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 0.875rem;
            padding: 1.15rem;
            transition: all 0.2s ease;
            display: flex;
            gap: 1rem;
            position: relative;
        }
        .dark .pedoman-step-card {
            background: #111827;
            border-color: #1f2937;
        }
        .pedoman-step-card:hover {
            transform: translateY(-2px);
            border-color: #cbd5e1;
            box-shadow: 0 6px 16px -2px rgba(0, 0, 0, 0.06);
        }
        .dark .pedoman-step-card:hover {
            border-color: #374151;
            box-shadow: 0 6px 16px -2px rgba(0, 0, 0, 0.35);
        }
        .pedoman-step-badge {
            width: 36px;
            height: 36px;
            min-width: 36px;
            border-radius: 0.65rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
            font-weight: 700;
        }
        .step-b1 { background: #eff6ff; color: #0054A6; border: 1px solid #bfdbfe; }
        .dark .step-b1 { background: rgba(0, 84, 166, 0.2); color: #60a5fa; border-color: rgba(96, 165, 250, 0.3); }

        .step-b2 { background: #eef2ff; color: #4f46e5; border: 1px solid #c7d2fe; }
        .dark .step-b2 { background: rgba(79, 70, 229, 0.2); color: #818cf8; border-color: rgba(129, 140, 248, 0.3); }

        .step-b3 { background: #fffbeb; color: #d97706; border: 1px solid #fde68a; }
        .dark .step-b3 { background: rgba(217, 119, 6, 0.2); color: #fbbf24; border-color: rgba(251, 191, 36, 0.3); }

        .step-b4 { background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; }
        .dark .step-b4 { background: rgba(5, 150, 105, 0.2); color: #34d399; border-color: rgba(52, 211, 153, 0.3); }

        .step-b5 { background: #f0fdfa; color: #0d9488; border: 1px solid #99f6e4; }
        .dark .step-b5 { background: rgba(13, 148, 136, 0.2); color: #2dd4bf; border-color: rgba(45, 212, 191, 0.3); }

        .step-b6 { background: #faf5ff; color: #7c3aed; border: 1px solid #e9d5ff; }
        .dark .step-b6 { background: rgba(124, 58, 237, 0.2); color: #a78bfa; border-color: rgba(167, 139, 250, 0.3); }

        .pedoman-step-body {
            flex: 1;
        }
        .pedoman-step-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 0.35rem;
            flex-wrap: wrap;
            gap: 0.35rem;
        }
        .pedoman-step-title {
            font-size: 0.92rem;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
        }
        .dark .pedoman-step-title {
            color: #f8fafc;
        }
        .pedoman-step-tag {
            font-size: 0.7rem;
            font-weight: 600;
            padding: 0.15rem 0.5rem;
            border-radius: 9999px;
            background: #f1f5f9;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }
        .dark .pedoman-step-tag {
            background: #1f2937;
            color: #94a3b8;
        }
        .pedoman-step-desc {
            font-size: 0.82rem;
            color: #64748b;
            margin: 0;
            line-height: 1.5;
        }
        .dark .pedoman-step-desc {
            color: #94a3b8;
        }
        .pedoman-step-desc strong {
            color: #1e293b;
            font-weight: 600;
        }
        .dark .pedoman-step-desc strong {
            color: #e2e8f0;
        }

        /* Pro Tip Callout */
        .pedoman-callout {
            margin-top: 1.25rem;
            border-radius: 0.75rem;
            padding: 0.9rem 1.15rem;
            background: #f0f7ff;
            border: 1px solid #bae6fd;
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            font-size: 0.82rem;
            color: #0369a1;
            line-height: 1.45;
        }
        .dark .pedoman-callout {
            background: rgba(14, 165, 233, 0.08);
            border-color: rgba(14, 165, 233, 0.2);
            color: #7dd3fc;
        }
        .pedoman-callout svg {
            width: 20px;
            height: 20px;
            min-width: 20px;
            color: #0284c7;
            margin-top: 1px;
        }
        .dark .pedoman-callout svg {
            color: #38bdf8;
        }

        /* Jalur Transaksi 3 Cards */
        .pedoman-jalur-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
        }
        @media (min-width: 900px) {
            .pedoman-jalur-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }
        .pedoman-jalur-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 0.875rem;
            padding: 1.25rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: all 0.2s ease;
        }
        .dark .pedoman-jalur-card {
            background: #111827;
            border-color: #1f2937;
        }
        .pedoman-jalur-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px -4px rgba(0, 0, 0, 0.08);
        }
        .dark .pedoman-jalur-card:hover {
            box-shadow: 0 8px 20px -4px rgba(0, 0, 0, 0.4);
        }
        .jalur-icon-wrap {
            width: 40px;
            height: 40px;
            border-radius: 0.65rem;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 0.85rem;
        }
        .jalur-icon-wrap svg {
            width: 22px;
            height: 22px;
        }
        .jalur-green { background: #ecfdf5; color: #059669; }
        .dark .jalur-green { background: rgba(5, 150, 105, 0.15); color: #34d399; }
        .jalur-amber { background: #fffbeb; color: #d97706; }
        .dark .jalur-amber { background: rgba(217, 119, 6, 0.15); color: #fbbf24; }
        .jalur-purple { background: #faf5ff; color: #7c3aed; }
        .dark .jalur-purple { background: rgba(124, 58, 237, 0.15); color: #a78bfa; }

        .jalur-title {
            font-size: 1rem;
            font-weight: 700;
            margin: 0 0 0.35rem 0;
            color: #0f172a;
        }
        .dark .jalur-title {
            color: #f8fafc;
        }
        .jalur-badge {
            display: inline-block;
            font-size: 0.72rem;
            font-weight: 700;
            padding: 0.2rem 0.55rem;
            border-radius: 0.375rem;
            margin-bottom: 0.85rem;
            width: fit-content;
        }
        .badge-green { background: #dcfce7; color: #15803d; }
        .dark .badge-green { background: rgba(21, 128, 61, 0.25); color: #86efac; }
        .badge-amber { background: #fef3c7; color: #b45309; }
        .dark .badge-amber { background: rgba(180, 83, 9, 0.25); color: #fde047; }
        .badge-purple { background: #f3e8ff; color: #6b21a8; }
        .dark .badge-purple { background: rgba(107, 33, 168, 0.25); color: #d8b4fe; }

        .jalur-desc {
            font-size: 0.82rem;
            color: #64748b;
            line-height: 1.5;
            margin-bottom: 1rem;
        }
        .dark .jalur-desc {
            color: #94a3b8;
        }
        .jalur-rule {
            background: #f8fafc;
            border-radius: 0.5rem;
            padding: 0.75rem;
            font-size: 0.8rem;
            color: #334155;
            line-height: 1.45;
            border-left: 3px solid;
        }
        .dark .jalur-rule {
            background: #1e293b;
            color: #cbd5e1;
        }
        .rule-green { border-color: #10b981; }
        .rule-amber { border-color: #f59e0b; }
        .rule-purple { border-color: #8b5cf6; }

        /* Format Excel Cards */
        .pedoman-format-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 0.875rem;
            padding: 1.15rem;
            margin-bottom: 1rem;
            transition: all 0.2s ease;
        }
        .dark .pedoman-format-card {
            background: #111827;
            border-color: #1f2937;
        }
        .pedoman-format-card:hover {
            border-color: #cbd5e1;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }
        .dark .pedoman-format-card:hover {
            border-color: #374151;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        }
        .format-card-header {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            margin-bottom: 0.75rem;
        }
        .format-icon {
            width: 32px;
            height: 32px;
            min-width: 32px;
            border-radius: 0.5rem;
            background: #f1f5f9;
            color: #0054A6;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .dark .format-icon {
            background: #1f2937;
            color: #38bdf8;
        }
        .format-icon svg {
            width: 18px;
            height: 18px;
        }
        .format-title {
            font-size: 0.95rem;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
        }
        .dark .format-title {
            color: #f8fafc;
        }
        .code-pill {
            display: inline-block;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 0.76rem;
            font-weight: 600;
            padding: 0.15rem 0.45rem;
            border-radius: 0.35rem;
            background: #fce7f3;
            color: #be185d;
            border: 1px solid #fbcfe8;
        }
        .dark .code-pill {
            background: rgba(190, 24, 93, 0.15);
            color: #f472b6;
            border-color: rgba(244, 114, 182, 0.25);
        }
        .tax-chip {
            display: inline-flex;
            align-items: center;
            font-size: 0.74rem;
            font-weight: 600;
            padding: 0.15rem 0.45rem;
            border-radius: 0.35rem;
            background: #f1f5f9;
            color: #334155;
            margin: 0.1rem;
            border: 1px solid #e2e8f0;
        }
        .dark .tax-chip {
            background: #1f2937;
            color: #cbd5e1;
            border-color: #374151;
        }

        /* FAQ Accordion */
        .pedoman-faq-item {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            margin-bottom: 0.75rem;
            overflow: hidden;
            transition: all 0.2s ease;
        }
        .dark .pedoman-faq-item {
            background: #111827;
            border-color: #1f2937;
        }
        .pedoman-faq-item:hover {
            border-color: #cbd5e1;
        }
        .dark .pedoman-faq-item:hover {
            border-color: #374151;
        }
        .pedoman-faq-trigger {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 1.15rem;
            background: transparent;
            border: none;
            cursor: pointer;
            text-align: left;
            gap: 0.75rem;
        }
        .faq-q-text {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            font-size: 0.88rem;
            font-weight: 600;
            color: #0f172a;
        }
        .dark .faq-q-text {
            color: #f8fafc;
        }
        .faq-q-badge {
            width: 22px;
            height: 22px;
            min-width: 22px;
            border-radius: 0.375rem;
            background: #eff6ff;
            color: #0054A6;
            font-size: 0.75rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .dark .faq-q-badge {
            background: rgba(0, 84, 166, 0.25);
            color: #60a5fa;
        }
        .faq-chevron {
            width: 18px;
            height: 18px;
            min-width: 18px;
            color: #94a3b8;
            transition: transform 0.2s ease;
        }
        .faq-answer {
            padding: 0 1.15rem 1rem 3rem;
            font-size: 0.84rem;
            color: #64748b;
            line-height: 1.55;
        }
        .dark .faq-answer {
            color: #94a3b8;
        }
        .faq-answer strong {
            color: #1e293b;
        }
        .dark .faq-answer strong {
            color: #e2e8f0;
        }
    </style>

    <!-- Header Hero Banner -->
    <div class="pedoman-hero">
        <div class="pedoman-hero-content">
            <div class="pedoman-hero-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
            </div>
            <div>
                <h4 class="pedoman-hero-title">Pedoman Operasional Rekap SP2D & Potongan Pajak</h4>
                <p class="pedoman-hero-desc">
                    Alur kerja bulanan, panduan format impor excel rincian, aturan validasi transaksi, dan cetak rekonsiliasi per entitas.
                </p>
            </div>
        </div>
        <a 
            href="{{ route('docs.pedoman-sp2d') }}" 
            target="_blank"
            class="pedoman-download-btn"
            title="Unduh Berkas Buku Panduan Resmi format Microsoft Word"
        >
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
            </svg>
            <span>Unduh Dokumen Pedoman (.docx)</span>
        </a>
    </div>

    <!-- Modern Segmented Tabs -->
    <div class="pedoman-tabs">
        <button 
            type="button" 
            class="pedoman-tab-btn" 
            :class="{ 'is-active': activeTab === 'sop' }"
            @click="activeTab = 'sop'"
        >
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
            </svg>
            <span>1. Alur & SOP Bulanan</span>
        </button>

        <button 
            type="button" 
            class="pedoman-tab-btn" 
            :class="{ 'is-active': activeTab === 'jalur' }"
            @click="activeTab = 'jalur'"
        >
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
            </svg>
            <span>2. Jalur Transaksi</span>
        </button>

        <button 
            type="button" 
            class="pedoman-tab-btn" 
            :class="{ 'is-active': activeTab === 'excel' }"
            @click="activeTab = 'excel'"
        >
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
            </svg>
            <span>3. Format Excel Rincian</span>
        </button>

        <button 
            type="button" 
            class="pedoman-tab-btn" 
            :class="{ 'is-active': activeTab === 'faq' }"
            @click="activeTab = 'faq'"
        >
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span>4. Tanya & Jawab</span>
        </button>
    </div>

    <!-- TAB 1: SOP BULANAN (6 Langkah) -->
    <div x-show="activeTab === 'sop'" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
        <div class="pedoman-grid">
            <!-- Langkah 1 -->
            <div class="pedoman-step-card">
                <div class="pedoman-step-badge step-b1">01</div>
                <div class="pedoman-step-body">
                    <div class="pedoman-step-header">
                        <h5 class="pedoman-step-title">Unduh MyIntress (T+1)</h5>
                        <span class="pedoman-step-tag">Portal DJPb</span>
                    </div>
                    <p class="pedoman-step-desc">
                        Unduh berkas <strong>Monitoring SPP SPM SP2D</strong> dan <strong>Monitoring Potongan SPM</strong> bulan berkenaan dari portal resmi MyIntress Kemenkeu.
                    </p>
                </div>
            </div>

            <!-- Langkah 2 -->
            <div class="pedoman-step-card">
                <div class="pedoman-step-badge step-b2">02</div>
                <div class="pedoman-step-body">
                    <div class="pedoman-step-header">
                        <h5 class="pedoman-step-title">Import ke Sistem</h5>
                        <span class="pedoman-step-tag">Data SP2D</span>
                    </div>
                    <p class="pedoman-step-desc">
                        Buka menu <strong>Data Rekap SP2D</strong>, klik tombol <strong>"Import SP2D MyIntress"</strong>, lalu unggah kedua berkas Excel tersebut secara bersamaan.
                    </p>
                </div>
            </div>

            <!-- Langkah 3 -->
            <div class="pedoman-step-card">
                <div class="pedoman-step-badge step-b3">03</div>
                <div class="pedoman-step-body">
                    <div class="pedoman-step-header">
                        <h5 class="pedoman-step-title">Filter 'Perlu Rincian'</h5>
                        <span class="pedoman-step-tag">Filter Tabel</span>
                    </div>
                    <p class="pedoman-step-desc">
                        Gunakan filter status pada tabel untuk menampilkan SP2D yang membutuhkan rincian penerima (SP2D Gaji Induk/Susulan, Tukin, Uang Makan, atau Lembur).
                    </p>
                </div>
            </div>

            <!-- Langkah 4 -->
            <div class="pedoman-step-card">
                <div class="pedoman-step-badge step-b4">04</div>
                <div class="pedoman-step-body">
                    <div class="pedoman-step-header">
                        <h5 class="pedoman-step-title">Unggah Rincian Excel</h5>
                        <span class="pedoman-step-tag">Aksi Baris</span>
                    </div>
                    <p class="pedoman-step-desc">
                        Klik tombol aksi <strong>"Upload Excel"</strong> pada baris SP2D berkenaan, pilih tipe berkas (Gaji, Tukin, Makan, atau Lembur), lalu proses berkasnya.
                    </p>
                </div>
            </div>

            <!-- Langkah 5 -->
            <div class="pedoman-step-card">
                <div class="pedoman-step-badge step-b5">05</div>
                <div class="pedoman-step-body">
                    <div class="pedoman-step-header">
                        <h5 class="pedoman-step-title">Verifikasi Keseimbangan</h5>
                        <span class="pedoman-step-tag">Auto Valid</span>
                    </div>
                    <p class="pedoman-step-desc">
                        Ketika total nominal rincian tepat <strong>100% klop</strong> dengan target potongan SP2D, sistem otomatis mengubah status transaksi menjadi <strong>Valid</strong>.
                    </p>
                </div>
            </div>

            <!-- Langkah 6 -->
            <div class="pedoman-step-card">
                <div class="pedoman-step-badge step-b6">06</div>
                <div class="pedoman-step-body">
                    <div class="pedoman-step-header">
                        <h5 class="pedoman-step-title">Cetak Rekap Per Pihak</h5>
                        <span class="pedoman-step-tag">LPJ / SPJ</span>
                    </div>
                    <p class="pedoman-step-desc">
                        Buka menu <strong>Rekap Per Pihak</strong>, tentukan filter bulan/tahun, lalu ekspor laporan ke <strong>PDF Ber-Bookmark</strong> atau <strong>Excel</strong> untuk lampiran LPJ.
                    </p>
                </div>
            </div>
        </div>

        <div class="pedoman-callout">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <div>
                <strong>Tips Efisiensi:</strong> SP2D Pembayaran Langsung (LS) Rekanan/Pihak Ketiga sudah otomatis <strong>Valid</strong> saat impor karena potongan pajaknya tercatat langsung di file MyIntress. Operator hanya perlu mengunggah rincian untuk SP2D berkategori Banyak Pihak (Gaji/Tukin/Makan/Lembur).
            </div>
        </div>
    </div>

    <!-- TAB 2: JALUR TRANSAKSI (3 Feature Cards) -->
    <div x-show="activeTab === 'jalur'" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
        <div class="pedoman-jalur-grid">
            <!-- 1 Pihak -->
            <div class="pedoman-jalur-card">
                <div>
                    <div class="jalur-icon-wrap jalur-green">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    <h5 class="jalur-title">1 Pihak (LS Rekanan)</h5>
                    <span class="jalur-badge badge-green">Otomatis Valid</span>
                    <p class="jalur-desc">
                        Transaksi belanja barang/modal kepada rekanan/vendor pihak ketiga tunggal. Potongan pajak sudah langsung tertera pada berkas <em>Monitoring Potongan SPM</em> MyIntress.
                    </p>
                </div>
                <div class="jalur-rule rule-green">
                    <strong>Aturan Sistem:</strong> Otomatis valid saat import jika data potongan ada di file MyIntress, atau jika nilai total potongan SP2D adalah Rp 0 (nihil).
                </div>
            </div>

            <!-- Banyak Pihak -->
            <div class="pedoman-jalur-card">
                <div>
                    <div class="jalur-icon-wrap jalur-amber">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <h5 class="jalur-title">Banyak Pihak (Gaji/Tukin)</h5>
                    <span class="jalur-badge badge-amber">Perlu Rincian → Valid</span>
                    <p class="jalur-desc">
                        Transaksi belanja pegawai jamak (Gaji, Tukin, Uang Makan, Lembur). Pada MyIntress hanya tercatat nilai akumulasi gelondongan atas nama kantor BPS.
                    </p>
                </div>
                <div class="jalur-rule rule-amber">
                    <strong>Aturan Sistem:</strong> Status awal <strong>Perlu Rincian</strong>. Otomatis beralih ke <strong>Valid</strong> begitu operator selesai mengunggah file rincian Excel dan totalnya klop 100%.
                </div>
            </div>

            <!-- UP / TUP -->
            <div class="pedoman-jalur-card">
                <div>
                    <div class="jalur-icon-wrap jalur-purple">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <h5 class="jalur-title">UP (Uang Persediaan)</h5>
                    <span class="jalur-badge badge-purple">Fleksibel / Manual Validasi</span>
                    <p class="jalur-desc">
                        Pencairan Uang Persediaan / TUP untuk operasional dinas. Potongan pajak sifatnya dinamis menyesuaikan kuitansi realisasi belanja riil bendahara pengeluaran.
                    </p>
                </div>
                <div class="jalur-rule rule-purple">
                    <strong>Aturan Sistem:</strong> <strong>Tidak terikat target kaku 100%</strong>. Operator/Bendahara memiliki otoritas mandiri menetapkan status verifikasi pada form edit SP2D.
                </div>
            </div>
        </div>
    </div>

    <!-- TAB 3: FORMAT EXCEL RINCIAN -->
    <div x-show="activeTab === 'excel'" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
        <div class="pedoman-grid">
            <!-- 1. Gaji Pusat -->
            <div class="pedoman-format-card">
                <div class="format-card-header">
                    <div class="format-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <h5 class="format-title">1. Daftar Gaji Pusat (Aplikasi GPP)</h5>
                </div>
                <p class="pedoman-step-desc" style="margin-bottom: 0.65rem;">
                    <strong>Kolom Header Wajib:</strong> <span class="code-pill">nmpeg</span>, serta salah satu dari <span class="code-pill">potpfk10</span> atau <span class="code-pill">iwp</span>. Identitas dibaca dari <span class="code-pill">nip</span> atau <span class="code-pill">npwp</span>.
                </p>
                <div style="font-size: 0.78rem; color: #64748b;">
                    <strong>Kode Akun Otomatis:</strong>
                    <div style="margin-top: 0.35rem; display: flex; flex-wrap: wrap; gap: 0.25rem;">
                        <span class="tax-chip">811311 PFK Bulanan</span>
                        <span class="tax-chip">811211 PFK 2%</span>
                        <span class="tax-chip">811111 IWP 8%</span>
                        <span class="tax-chip">811135 BPJS</span>
                        <span class="tax-chip">411121 PPh 21</span>
                        <span class="tax-chip">425151 Sewa Rumah</span>
                    </div>
                </div>
            </div>

            <!-- 2. Tukin -->
            <div class="pedoman-format-card">
                <div class="format-card-header">
                    <div class="format-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <h5 class="format-title">2. Daftar Tukin (Tunjangan Kinerja)</h5>
                </div>
                <p class="pedoman-step-desc" style="margin-bottom: 0.65rem;">
                    <strong>Kolom Header Wajib:</strong> <span class="code-pill">nama_pegawai</span> (atau <span class="code-pill">nmpeg</span> / <span class="code-pill">nama</span>), <span class="code-pill">pajak</span>, dan <span class="code-pill">nip</span> / <span class="code-pill">npwp</span>.
                </p>
                <div style="font-size: 0.78rem; color: #64748b;">
                    <strong>Pemetaan Akun:</strong>
                    <div style="margin-top: 0.35rem;">
                        <span class="tax-chip">411121 PPh Pasal 21 (Otomatis)</span>
                    </div>
                </div>
            </div>

            <!-- 3. Uang Makan -->
            <div class="pedoman-format-card">
                <div class="format-card-header">
                    <div class="format-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <h5 class="format-title">3. Daftar Uang Makan</h5>
                </div>
                <p class="pedoman-step-desc" style="margin-bottom: 0.65rem;">
                    <strong>Kolom Header Wajib:</strong> <span class="code-pill">nmpeg</span> (atau <span class="code-pill">nama_pegawai</span>), <span class="code-pill">potongan</span>, dan <span class="code-pill">nip</span> / <span class="code-pill">npwp</span>.
                </p>
                <div style="font-size: 0.78rem; color: #64748b;">
                    <strong>Pemetaan Akun:</strong>
                    <div style="margin-top: 0.35rem;">
                        <span class="tax-chip">411121 PPh Pasal 21 (Otomatis)</span>
                    </div>
                </div>
            </div>

            <!-- 4. Uang Lembur -->
            <div class="pedoman-format-card">
                <div class="format-card-header">
                    <div class="format-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <h5 class="format-title">4. Daftar Uang Lembur</h5>
                </div>
                <p class="pedoman-step-desc" style="margin-bottom: 0.65rem;">
                    <strong>Kolom Header Wajib:</strong> <span class="code-pill">pajak</span>, serta <span class="code-pill">nmpeg</span> atau <span class="code-pill">nmrek</span>, dan <span class="code-pill">nip</span> / <span class="code-pill">npwp</span>.
                </p>
                <div style="font-size: 0.78rem; color: #64748b;">
                    <strong>Pemetaan Akun:</strong>
                    <div style="margin-top: 0.35rem;">
                        <span class="tax-chip">411121 PPh Pasal 21 (Otomatis)</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="pedoman-callout">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
            </svg>
            <div>
                <strong>Pencocokan Cerdas NPWP/NIK (Auto-Lookup):</strong> Sistem secara otomatis mendeteksi kolom identitas (<span class="code-pill">npwp</span>, <span class="code-pill">nip</span>, atau <span class="code-pill">nik</span>). Jika nomor NPWP pegawai pada file Excel belum terisi atau kosong, sistem akan otomatis menyinkronkan data NPWP/NIK dari master pegawai BPS.
            </div>
        </div>
    </div>

    <!-- TAB 4: TANYA & JAWAB (FAQ Accordion) -->
    <div x-show="activeTab === 'faq'" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
        <!-- FAQ 1 -->
        <div class="pedoman-faq-item">
            <button type="button" class="pedoman-faq-trigger" @click="activeFaq = (activeFaq === 1 ? null : 1)">
                <div class="faq-q-text">
                    <span class="faq-q-badge">Q1</span>
                    <span>Kenapa import MyIntress gagal dengan pesan "Kolom No. SP2D tidak ditemukan"?</span>
                </div>
                <svg class="faq-chevron" :style="activeFaq === 1 ? 'transform: rotate(180deg)' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            <div x-show="activeFaq === 1" x-collapse class="faq-answer">
                <strong>Penyebab:</strong> File Excel tertukar posisi unggah.<br>
                <strong>Solusi:</strong> Pastikan pada input pertama Anda memilih berkas <em>Monitoring SPP SPM SP2D</em>, dan pada input kedua memilih berkas <em>Monitoring Potongan SPM</em> yang diekspor dari portal MyIntress.
            </div>
        </div>

        <!-- FAQ 2 -->
        <div class="pedoman-faq-item">
            <button type="button" class="pedoman-faq-trigger" @click="activeFaq = (activeFaq === 2 ? null : 2)">
                <div class="faq-q-text">
                    <span class="faq-q-badge">Q2</span>
                    <span>Mengapa status SP2D tetap "Perlu Rincian" padahal semua baris sudah terisi?</span>
                </div>
                <svg class="faq-chevron" :style="activeFaq === 2 ? 'transform: rotate(180deg)' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            <div x-show="activeFaq === 2" x-collapse class="faq-answer">
                <strong>Penyebab:</strong> Selisih pecahan pembulatan rupiah.<br>
                <strong>Solusi:</strong> Periksa badge indikator pada baris SP2D. Jika ada selisih walau hanya Rp 1 (lazim terjadi karena pembulatan pecahan desimal pada file Gaji/Tukin), edit salah satu baris rincian agar total nominal rincian tepat 100% klop dengan target potongan SP2D.
            </div>
        </div>

        <!-- FAQ 3 -->
        <div class="pedoman-faq-item">
            <button type="button" class="pedoman-faq-trigger" @click="activeFaq = (activeFaq === 3 ? null : 3)">
                <div class="faq-q-text">
                    <span class="faq-q-badge">Q3</span>
                    <span>Mengapa berkas PDF / Excel hasil export Rekap Per Pihak tidak otomatis terunduh?</span>
                </div>
                <svg class="faq-chevron" :style="activeFaq === 3 ? 'transform: rotate(180deg)' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            <div x-show="activeFaq === 3" x-collapse class="faq-answer">
                <strong>Penyebab:</strong> Terblokir oleh fitur Pop-up browser.<br>
                <strong>Solusi:</strong> Periksa bilah alamat (address bar) browser Anda pada pojok kanan atas, klik ikon pop-up yang diblokir, dan pilih <strong>"Always allow pop-ups and redirects from this site"</strong> (Izinkan Pop-up).
            </div>
        </div>

        <!-- FAQ 4 -->
        <div class="pedoman-faq-item">
            <button type="button" class="pedoman-faq-trigger" @click="activeFaq = (activeFaq === 4 ? null : 4)">
                <div class="faq-q-text">
                    <span class="faq-q-badge">Q4</span>
                    <span>Bagaimana jika penerima honor/pihak ketiga belum memiliki NPWP?</span>
                </div>
                <svg class="faq-chevron" :style="activeFaq === 4 ? 'transform: rotate(180deg)' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            <div x-show="activeFaq === 4" x-collapse class="faq-answer">
                <strong>Ketentuan:</strong> Sesuai regulasi perpajakan NIK (16 digit) sebagai NPWP format baru.<br>
                <strong>Solusi:</strong> Masukkan 16 digit Nomor Induk Kependudukan (NIK KTP) pada kolom identitas. Sistem secara otomatis mencatatnya sebagai tanda pengenal wajib pajak yang sah untuk rekapitulasi pelaporan.
            </div>
        </div>
    </div>
</div>
