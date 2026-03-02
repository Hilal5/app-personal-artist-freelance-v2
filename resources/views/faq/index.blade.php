@extends('layouts.app')
@section('title', 'FAQ — ArtSpace')

@push('styles')
<style>
    .faq-item {
        border-radius: 14px; border: 1px solid;
        overflow: hidden; transition: all 0.2s;
        margin-bottom: 8px;
    }
    .dark .faq-item { background: #2a2a3d; border-color: #3a3a50; }
    html:not(.dark) .faq-item { background: white; border-color: #e5e7eb; }
    .faq-item:hover { border-color: #f97316; }

    .faq-question {
        width: 100%; display: flex; align-items: center;
        justify-content: space-between; gap: 12px;
        padding: 16px 20px; cursor: pointer;
        background: none; border: none; text-align: left;
    }

    .faq-answer {
        overflow: hidden;
        max-height: 0;
        transition: max-height 0.35s ease, padding 0.3s ease;
        padding: 0 20px;
    }
    .faq-answer.open {
        max-height: 500px;
        padding: 0 20px 16px;
    }

    .faq-icon {
        width: 28px; height: 28px; border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0; transition: all 0.2s;
        background: rgba(249,115,22,0.12);
    }
    .faq-icon.open { background: #f97316; }

    .cat-tab {
        padding: 6px 16px; border-radius: 99px;
        font-size: 0.72rem; font-weight: 700;
        cursor: pointer; transition: all 0.2s;
        border: 1px solid transparent; white-space: nowrap;
    }
    .dark .cat-tab  { background: #2a2a3d; color: #9ca3af; border-color: #3a3a50; }
    html:not(.dark) .cat-tab { background: #f3f4f6; color: #6b7280; border-color: #e5e7eb; }
    .cat-tab.active { background: #f97316 !important; color: white !important; border-color: #f97316 !important; }
    .cat-tab:hover:not(.active) { border-color: #f97316; color: #f97316; }

    .search-highlight { background: rgba(249,115,22,0.25); border-radius: 3px; }
</style>
@endpush

@section('content')
<div x-data="faqPage()" x-init="init()">

    {{-- Header --}}
    <div class="text-center mb-8">
        <h1 class="text-2xl font-bold mb-2" :class="isDark ? 'text-white' : 'text-[#21212e]'">
            Frequently Asked Questions
        </h1>
        <p class="text-sm" :class="isDark ? 'text-gray-400' : 'text-gray-500'">
            Temukan jawaban atas pertanyaan yang sering ditanyakan
        </p>
    </div>

    {{-- Search --}}
    <div class="max-w-xl mx-auto mb-6">
        <div class="flex items-center gap-3 px-4 py-3 rounded-2xl border"
            :class="isDark ? 'bg-[#2a2a3d] border-[#3a3a50]' : 'bg-white border-gray-200'">
            <i data-lucide="search" class="w-4 h-4 text-orange-500 shrink-0"></i>
            <input type="text" placeholder="Cari pertanyaan..." x-model="search"
                @input="filterFaq()"
                class="bg-transparent text-sm outline-none w-full"
                :class="isDark ? 'text-white placeholder-gray-600' : 'text-[#21212e] placeholder-gray-400'">
            <button x-show="search" @click="search = ''; filterFaq()"
                class="text-gray-400 hover:text-orange-500 transition-all">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
    </div>

    {{-- Category tabs --}}
    <div class="flex gap-2 flex-wrap justify-center mb-8" x-show="!search">
        <button class="cat-tab" :class="activeCategory === 'all' ? 'active' : ''"
            @click="activeCategory = 'all'">
            <i data-lucide="layout-grid" style="width:11px;height:11px;display:inline;margin-right:4px;"></i>
            Semua
        </button>
        @foreach($categories as $cat)
        <button class="cat-tab" :class="activeCategory === '{{ addslashes($cat) }}' ? 'active' : ''"
            @click="activeCategory = '{{ addslashes($cat) }}'">
            {{ $cat }}
        </button>
        @endforeach
    </div>

    {{-- FAQ List --}}
    <div class="max-w-3xl mx-auto">

        {{-- Search results --}}
        <div x-show="search">
            <p class="text-xs mb-4" :class="isDark ? 'text-gray-400' : 'text-gray-500'">
                Menampilkan hasil untuk "<span class="text-orange-500 font-bold" x-text="search"></span>"
            </p>
            <div id="searchResults"></div>
            <div id="noSearchResult" class="hidden text-center py-12">
                <i data-lucide="search-x" class="w-10 h-10 text-gray-400 mx-auto mb-3"></i>
                <p class="font-bold" :class="isDark ? 'text-white' : 'text-[#21212e]'">Tidak ada hasil</p>
                <p class="text-xs mt-1" :class="isDark ? 'text-gray-500' : 'text-gray-400'">Coba kata kunci lain</p>
            </div>
        </div>

        {{-- Category groups --}}
        <div x-show="!search">
            @foreach($faqs as $category => $items)
            <div class="faq-group mb-6" data-category="{{ $category }}">
                <h2 class="font-bold text-sm mb-3 flex items-center gap-2"
                    :class="isDark ? 'text-white' : 'text-[#21212e]'">
                    <span class="w-1 h-4 bg-orange-500 rounded-full inline-block"></span>
                    {{ $category }}
                    <span class="text-xs font-normal px-2 py-0.5 rounded-full"
                        :class="isDark ? 'bg-[#3a3a50] text-gray-400' : 'bg-gray-100 text-gray-500'">
                        {{ $items->count() }}
                    </span>
                </h2>

                @foreach($items as $faq)
                <div class="faq-item" id="faq-{{ $faq->id }}">
                    <button class="faq-question"
                        onclick="toggleFaq({{ $faq->id }})"
                        id="faqbtn-{{ $faq->id }}">
                        <span class="text-sm font-bold text-left flex-1"
                            style="{{ $loop->parent->first && $loop->first ? '' : '' }}"
                            :class="isDark ? 'text-white' : 'text-[#21212e]'">
                            {{ $faq->question }}
                        </span>
                        <div class="faq-icon" id="faqicon-{{ $faq->id }}">
                            <i data-lucide="plus" style="width:14px;height:14px;color:#f97316;" id="faqiconsvg-{{ $faq->id }}"></i>
                        </div>
                    </button>
                    <div class="faq-answer" id="faqans-{{ $faq->id }}">
                        <p class="text-sm leading-relaxed" :class="isDark ? 'text-gray-300' : 'text-gray-600'">
                            {{ $faq->answer }}
                        </p>
                    </div>
                </div>
                @endforeach
            </div>
            @endforeach
        </div>

{{-- CTA bottom --}}
<div class="mt-8 p-5 rounded-2xl border text-center"
    :class="isDark ? 'bg-[#2a2a3d] border-[#3a3a50]' : 'bg-white border-gray-200'">
    <i data-lucide="message-circle" class="w-8 h-8 text-orange-500 mx-auto mb-2"></i>
    <p class="font-bold text-sm mb-1" :class="isDark ? 'text-white' : 'text-[#21212e]'">
        Masih ada pertanyaan?
    </p>
    <p class="text-xs mb-3" :class="isDark ? 'text-gray-400' : 'text-gray-500'">
        Hubungi langsung melalui fitur chat
    </p>

    @if(session('user_id'))
    <a href="{{ route('chat.index') }}"
        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-orange-500 text-white text-xs font-bold hover:bg-orange-600 transition-all">
        <i data-lucide="message-circle" class="w-4 h-4"></i> Chat Sekarang
    </a>
    @else
    <button id="btnOpenLogin"
        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-orange-500 text-white text-xs font-bold hover:bg-orange-600 transition-all">
        <i data-lucide="message-circle" class="w-4 h-4"></i> Chat Sekarang
    </button>
    @endif
</div>

        {{-- Artist: manage link --}}
        @if(session('user_role') === 'artist')
        <div class="mt-3 text-center">
            <a href="{{ route('faq.manage') }}"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold transition-all"
                :class="isDark ? 'bg-[#2a2a3d] text-gray-300 hover:text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'">
                <i data-lucide="settings" class="w-3.5 h-3.5"></i> Manage FAQ
            </a>
        </div>
        @endif
    </div>

</div>
@endsection

@push('scripts')
<script>
// Data FAQ untuk search
const faqData = [
    @foreach($faqs->flatten() as $faq)
    {
        id: {{ $faq->id }},
        category: "{{ addslashes($faq->category) }}",
        question: "{{ addslashes($faq->question) }}",
        answer: "{{ addslashes($faq->answer) }}",
    },
    @endforeach
];

let openFaqId = null;

function toggleFaq(id) {
    const ans  = document.getElementById('faqans-' + id);
    const icon = document.getElementById('faqicon-' + id);
    const svg  = document.getElementById('faqiconsvg-' + id);

    // Tutup yang sebelumnya
    if (openFaqId && openFaqId !== id) {
        const prevAns  = document.getElementById('faqans-' + openFaqId);
        const prevIcon = document.getElementById('faqicon-' + openFaqId);
        const prevSvg  = document.getElementById('faqiconsvg-' + openFaqId);
        if (prevAns)  prevAns.classList.remove('open');
        if (prevIcon) prevIcon.classList.remove('open');
        if (prevSvg) {
            prevSvg.setAttribute('data-lucide', 'plus');
            prevSvg.style.color = '#f97316';
            lucide.createIcons();
        }
        openFaqId = null;
    }

    const isOpen = ans.classList.contains('open');
    ans.classList.toggle('open', !isOpen);
    icon.classList.toggle('open', !isOpen);

    if (!isOpen) {
        svg.setAttribute('data-lucide', 'minus');
        svg.style.color = 'white';
        openFaqId = id;
    } else {
        svg.setAttribute('data-lucide', 'plus');
        svg.style.color = '#f97316';
        openFaqId = null;
    }
    lucide.createIcons();
}

function faqPage() {
    return {
        isDark: false,
        search: '',
        activeCategory: 'all',

        filterFaq() {
            const q       = this.search.toLowerCase().trim();
            const results = document.getElementById('searchResults');
            const noRes   = document.getElementById('noSearchResult');

            if (!q) return;

            const matched = faqData.filter(f =>
                f.question.toLowerCase().includes(q) ||
                f.answer.toLowerCase().includes(q)
            );

            if (matched.length === 0) {
                results.innerHTML = '';
                noRes.classList.remove('hidden');
                return;
            }

            noRes.classList.add('hidden');
            const isDark = document.documentElement.classList.contains('dark');

            results.innerHTML = matched.map(f => {
                const hl = str => str.replace(
                    new RegExp(q, 'gi'),
                    m => `<mark class="search-highlight">${m}</mark>`
                );
                return `
                <div class="faq-item mb-2">
                    <button class="faq-question" onclick="toggleFaq(${f.id})" id="faqbtn-${f.id}">
                        <span class="text-sm font-bold text-left flex-1 ${isDark ? 'text-white' : 'text-[#21212e]'}">
                            ${hl(f.question)}
                        </span>
                        <div class="faq-icon" id="faqicon-${f.id}">
                            <i data-lucide="plus" style="width:14px;height:14px;color:#f97316;" id="faqiconsvg-${f.id}"></i>
                        </div>
                    </button>
                    <div class="faq-answer" id="faqans-${f.id}">
                        <p class="text-xs mb-1" style="color:#f97316;font-weight:700;">${f.category}</p>
                        <p class="text-sm leading-relaxed ${isDark ? 'text-gray-300' : 'text-gray-600'}">
                            ${hl(f.answer)}
                        </p>
                    </div>
                </div>`;
            }).join('');

            lucide.createIcons();
        },

        init() {
            this.isDark = document.documentElement.classList.contains('dark');
            new MutationObserver(() => {
                this.isDark = document.documentElement.classList.contains('dark');

                // Filter category
                document.querySelectorAll('.faq-group').forEach(group => {
                    const show = this.activeCategory === 'all' ||
                        group.dataset.category === this.activeCategory;
                    group.style.display = show ? '' : 'none';
                });
            }).observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });

            // Watch activeCategory
            this.$watch('activeCategory', val => {
                document.querySelectorAll('.faq-group').forEach(group => {
                    const show = val === 'all' || group.dataset.category === val;
                    group.style.display = show ? '' : 'none';
                });
            });

            lucide.createIcons();
        }
    }
}

// Trigger modal login dari navbar
const btnLogin = document.getElementById('btnOpenLogin');
if (btnLogin) {
    btnLogin.addEventListener('click', function () {
        const nav = document.querySelector('header[x-data]');
        if (nav && nav._x_dataStack) {
            nav._x_dataStack[0].openLogin = true;
        }
    });
}
</script>
@endpush