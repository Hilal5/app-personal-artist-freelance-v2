@extends('layouts.app')
@section('title', 'Chat — ArtSpace')

@push('styles')
<style>
    /* Dark mode bubble text */
.dark .bubble-mine,
.dark .bubble-artist {
    color: #ffffff;
}

/* Light mode bubble text */
html:not(.dark) .bubble-mine,
html:not(.dark) .bubble-artist {
    color: #21212e;
}

    .bubble-artist {
        background: rgba(249,115,22,0.15);
        border: 1px solid rgba(249,115,22,0.25);
        border-radius: 18px 18px 18px 4px;
    }
    .bubble-mine { border-radius: 18px 18px 4px 18px; }
    .dark .bubble-mine { background: #2a2a3d; border: 1px solid #3a3a50; }
    html:not(.dark) .bubble-mine { background: #f3f4f6; border: 1px solid #e5e7eb; }

    .input-box { border-radius: 16px; transition: all 0.2s; }
    .dark .input-box { background: #2a2a3d; border: 1px solid #3a3a50; }
    html:not(.dark) .input-box { background: #f9fafb; border: 1px solid #e5e7eb; }
    .dark .input-box:focus-within { border-color: #f97316; box-shadow: 0 0 0 3px rgba(249,115,22,0.15); }
    html:not(.dark) .input-box:focus-within { border-color: #21212e; box-shadow: 0 0 0 3px rgba(33,33,46,0.08); }

    .online-dot { width:10px; height:10px; background:#22c55e; border-radius:99px; border:2px solid; }
    .dark .online-dot { border-color:#21212e; }
    html:not(.dark) .online-dot { border-color:white; }

    .contact-item { border-radius:12px; cursor:pointer; transition:all 0.18s; }
    .dark .contact-item:hover, .dark .contact-item.active { background:rgba(249,115,22,0.12); }
    html:not(.dark) .contact-item:hover, html:not(.dark) .contact-item.active { background:#f3f4f6; }

    .file-badge {
        display:inline-flex; align-items:center; gap:6px;
        padding:6px 12px; border-radius:10px;
        font-size:0.75rem; font-weight:600;
        border:1px solid rgba(249,115,22,0.3);
        background:rgba(249,115,22,0.1);
        color:#f97316; text-decoration:none; max-width:220px;
    }

    /* Mobile sidebar */
    @media (max-width: 1023px) {
        .sidebar-mobile {
            position:fixed; top:0; left:0;
            width:80%; max-width:300px; height:100vh;
            z-index:1000;
            transform:translateX(-100%);
            transition:transform 0.3s ease;
        }
        .sidebar-mobile.open { transform:translateX(0); }
        .overlay-mobile { position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:999; display:none; }
        .overlay-mobile.show { display:block; }
    }

    /* Message wrapper */
    .msg-row {
        display: flex;
        gap: 10px;
        align-items: flex-end;
        position: relative;
    }
    .msg-row.mine { flex-direction: row-reverse; }

    /* Delete button — muncul saat hover row */
    .msg-delete-btn {
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.15s;
        width: 26px;
        height: 26px;
        border-radius: 8px;
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        align-self: center;
    }
    .dark .msg-delete-btn { background: #3a3a50; color: #9ca3af; }
    html:not(.dark) .msg-delete-btn { background: #f3f4f6; color: #9ca3af; }
    .msg-delete-btn:hover { background: rgba(239,68,68,0.15) !important; color: #ef4444 !important; }
    .msg-row:hover .msg-delete-btn {
        opacity: 1;
        pointer-events: all;
    }

    .blocked-banner {
        padding:12px 16px; border-radius:12px; font-size:0.8rem; font-weight:600;
        background:rgba(239,68,68,0.1); border:1px solid rgba(239,68,68,0.3);
        color:#ef4444; text-align:center;
    }
</style>
@endpush

@section('content')
@php
    $isArtist   = session('user_role') === 'artist';
    $myId       = session('user_id');
    $receiverId = $isArtist ? ($activeContact->id ?? null) : ($artist->id ?? null);
    $isBlocked  = $activeContact?->is_blocked ?? false;
@endphp

<div x-data="chatApp()" x-init="init()"
    class="flex gap-0 lg:gap-4 relative"
    style="height: calc(100vh - 130px);">

    <div class="overlay-mobile" id="overlay" @click="closeSidebar()"></div>

    {{-- SIDEBAR --}}
    <aside id="sidebar"
        class="sidebar-mobile lg:static lg:transform-none lg:w-64 xl:lg:w-72 shrink-0 flex flex-col overflow-hidden border rounded-2xl"
        :class="isDark ? 'bg-[#2a2a3d] border-[#3a3a50]' : 'bg-white border-gray-200'">

        <div class="p-4 border-b shrink-0 flex items-start justify-between gap-2"
            :class="isDark ? 'border-[#3a3a50]' : 'border-gray-100'">
            <div class="flex-1 min-w-0">
                <h2 class="font-bold text-sm mb-3" :class="isDark ? 'text-white' : 'text-[#21212e]'">
                    {{ $isArtist ? 'Conversations' : 'Chat' }}
                </h2>
                @if($isArtist)
                <div class="flex items-center gap-2 px-3 py-2 rounded-xl"
                    :class="isDark ? 'bg-[#21212e]' : 'bg-gray-100'">
                    <i data-lucide="search" class="w-4 h-4 text-gray-400 shrink-0"></i>
                    <input type="text" placeholder="Search..." x-model="query"
                        class="bg-transparent text-xs outline-none w-full"
                        :class="isDark ? 'text-white placeholder-gray-600' : 'text-[#21212e] placeholder-gray-400'">
                </div>
                @endif
            </div>
            <button @click="closeSidebar()"
                class="lg:hidden w-8 h-8 rounded-lg flex items-center justify-center shrink-0 mt-0.5 transition-all"
                :class="isDark ? 'text-gray-400 hover:bg-[#21212e] hover:text-white' : 'text-gray-400 hover:bg-gray-100'">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto p-3 flex flex-col gap-1">
            @if($isArtist)
                @forelse($contacts as $contact)
                <a href="{{ route('chat.index', ['contact' => $contact['id']]) }}"
                    class="contact-item p-3 flex items-center gap-3 {{ ($activeContact && $activeContact->id == $contact['id']) ? 'active' : '' }}"
                    @click="closeSidebar()">
                    <div class="relative shrink-0">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($contact['name']) }}&background=7c3aed&color=fff"
                            class="w-9 h-9 rounded-full object-cover">
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex justify-between items-center gap-1">
                            <p class="text-xs font-bold truncate" :class="isDark ? 'text-white' : 'text-[#21212e]'">
                                {{ $contact['name'] }}
                                @if($contact['is_blocked'])<span class="text-red-400 ml-1">🚫</span>@endif
                            </p>
                            @if($contact['last_time'])
                            <span class="text-xs shrink-0" :class="isDark ? 'text-gray-600' : 'text-gray-400'">
                                {{ \Carbon\Carbon::parse($contact['last_time'])->diffForHumans(null,true,true) }}
                            </span>
                            @endif
                        </div>
                        <p class="text-xs truncate mt-0.5" :class="isDark ? 'text-gray-500' : 'text-gray-400'">
                            {{ $contact['has_file'] ? '📎 File' : $contact['last_msg'] }}
                        </p>
                    </div>
                    @if($contact['unread'] > 0)
                    <span class="badge shrink-0">{{ $contact['unread'] }}</span>
                    @endif
                </a>
                @empty
                <p class="text-xs text-center py-4" :class="isDark ? 'text-gray-500' : 'text-gray-400'">Belum ada client</p>
                @endforelse
            @else
                <div class="contact-item active p-3 flex items-center gap-3">
                    <div class="relative shrink-0">
                        <img src="https://ui-avatars.com/api/?name=Admin+Artist&background=f97316&color=fff"
                            class="w-9 h-9 rounded-full object-cover">
                        <span class="online-dot absolute bottom-0 right-0"></span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-bold" :class="isDark ? 'text-white' : 'text-[#21212e]'">Admin Artist</p>
                        <p class="text-xs mt-0.5 text-green-500">Online</p>
                    </div>
                </div>
            @endif
        </div>
    </aside>

    {{-- CHAT WINDOW --}}
    <div class="flex-1 flex flex-col rounded-2xl overflow-hidden border w-full"
        :class="isDark ? 'bg-[#2a2a3d] border-[#3a3a50]' : 'bg-white border-gray-200'">

        {{-- Header --}}
        <div class="px-4 py-3.5 border-b flex items-center gap-3 shrink-0"
            :class="isDark ? 'border-[#3a3a50]' : 'border-gray-100'">

            <button @click="openSidebar()"
                class="lg:hidden w-8 h-8 rounded-lg flex items-center justify-center shrink-0 transition-all"
                :class="isDark ? 'text-gray-400 hover:bg-[#21212e] hover:text-orange-400' : 'text-gray-400 hover:bg-gray-100'">
                <i data-lucide="menu" class="w-4 h-4"></i>
            </button>

            @if($activeContact)
            <div class="relative shrink-0">
                <img src="https://ui-avatars.com/api/?name={{ urlencode($activeContact->name) }}&background={{ $isArtist ? '7c3aed' : 'f97316' }}&color=fff"
                    class="w-9 h-9 rounded-full object-cover">
                @if(!$isBlocked)<span class="online-dot absolute bottom-0 right-0"></span>@endif
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-bold truncate" :class="isDark ? 'text-white' : 'text-[#21212e]'">
                    {{ $activeContact->name }}
                    @if($isBlocked)<span class="text-red-400 text-xs ml-1">— Diblokir</span>@endif
                </p>
                <p class="text-xs {{ $isBlocked ? 'text-red-400' : 'text-green-500' }}">
                    {{ $isBlocked ? 'Diblokir' : 'Online' }}
                </p>
            </div>

            @if($isArtist)
            <div class="ml-auto relative" x-data="{ open: false }" @click.away="open = false">
                <button @click="open = !open"
                    class="w-8 h-8 rounded-lg flex items-center justify-center transition-all"
                    :class="isDark ? 'text-gray-400 hover:bg-[#21212e] hover:text-orange-400' : 'text-gray-400 hover:bg-gray-100'">
                    <i data-lucide="more-vertical" class="w-4 h-4"></i>
                </button>
                <div x-show="open"
                    class="absolute right-0 top-10 rounded-xl p-2 z-50 min-w-[170px]"
                    :class="isDark ? 'bg-[#2a2a3d] border border-[#3a3a50]' : 'bg-white border border-gray-200 shadow-lg'">
                    <button @click="toggleBlock({{ $activeContact->id }}); open = false"
                        class="w-full flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-bold transition-all"
                        :class="isDark ? 'text-red-400 hover:bg-red-500/10' : 'text-red-500 hover:bg-red-50'">
                        <i data-lucide="{{ $isBlocked ? 'user-check' : 'user-x' }}" class="w-4 h-4"></i>
                        {{ $isBlocked ? 'Unblokir User' : 'Blokir User' }}
                    </button>
                </div>
            </div>
            @else
            <div class="ml-auto">
                <button class="w-8 h-8 rounded-lg flex items-center justify-center transition-all"
                    :class="isDark ? 'text-gray-400 hover:bg-[#21212e]' : 'text-gray-400 hover:bg-gray-100'">
                    <i data-lucide="more-vertical" class="w-4 h-4"></i>
                </button>
            </div>
            @endif

            @else
            <div class="flex-1">
                <p class="text-sm" :class="isDark ? 'text-gray-400' : 'text-gray-500'">Pilih conversation</p>
            </div>
            @endif
        </div>

        {{-- Messages --}}
        <div class="flex-1 overflow-y-auto p-4 flex flex-col gap-3" id="msgs">
            @if($activeContact)
                @foreach($messages as $m)
                @php $mine = $m->sender_id == $myId; @endphp
                <div class="msg-row {{ $mine ? 'mine' : '' }}" id="msg-{{ $m->id }}">

                    <img src="https://ui-avatars.com/api/?name={{ urlencode($mine ? session('user_name') : $activeContact->name) }}&background={{ $mine ? '7c3aed' : 'f97316' }}&color=fff"
                        class="w-8 h-8 rounded-full object-cover shrink-0">

                    <div class="max-w-xs lg:max-w-md flex flex-col {{ $mine ? 'items-end' : 'items-start' }}">
                        @if($m->file_path)
                        @php $url = Storage::url($m->file_path); @endphp
                            @if(str_contains($m->file_type ?? '', 'image'))
                                <img src="{{ $url }}" class="max-w-xs rounded-xl object-cover max-h-48 mb-1 cursor-pointer" onclick="window.open(this.src)">
                            @elseif(str_contains($m->file_type ?? '', 'video'))
                                <video src="{{ $url }}" controls class="max-w-xs rounded-xl max-h-48 mb-1"></video>
                            @else
                                <a href="{{ $url }}" target="_blank" class="file-badge mb-1">
                                    <i data-lucide="file" class="w-4 h-4 shrink-0"></i>
                                    <span class="truncate">{{ $m->file_name }}</span>
                                </a>
                            @endif
                        @endif

                        @if($m->message)
                        <div class="px-4 py-2.5 text-sm {{ $mine ? 'bubble-mine' : 'bubble-artist' }}"
    :class="isDark ? 'text-white' : 'text-[#21212e]'">
                            {{ $m->message }}
                        </div>
                        @endif

                        <span class="text-xs mt-1 px-1" :class="isDark ? 'text-gray-600' : 'text-gray-400'">
                            {{ \Carbon\Carbon::parse($m->created_at)->format('H:i') }}
                        </span>
                    </div>

                {{-- Delete button: artist semua pesan, user hanya miliknya sendiri --}}
                @if($isArtist || $mine)
                <button class="msg-delete-btn" onclick="deleteMsg({{ $m->id }})" title="Hapus pesan">
                    <i data-lucide="trash-2" style="width:13px;height:13px;"></i>
                </button>
                @endif
                </div>
                @endforeach
            @else
                <div class="flex items-center justify-center h-full">
                    <div class="text-center">
                        <div class="w-16 h-16 rounded-2xl bg-orange-500/10 flex items-center justify-center mx-auto mb-3">
                            <i data-lucide="message-circle" class="w-8 h-8 text-orange-500"></i>
                        </div>
                        <p class="text-sm font-bold" :class="isDark ? 'text-white' : 'text-[#21212e]'">Pilih conversation</p>
                        <p class="text-xs mt-1" :class="isDark ? 'text-gray-500' : 'text-gray-400'">Klik nama client di sebelah kiri</p>
                    </div>
                </div>
            @endif
            <div id="new-msgs"></div>
        </div>

        {{-- Attachment preview --}}
        <div x-show="files.length > 0" class="px-4">
            <div class="flex flex-wrap gap-2 py-2 border-t" :class="isDark ? 'border-[#3a3a50]' : 'border-gray-100'">
                <template x-for="(f, i) in files" :key="i">
                    <div class="relative group rounded-xl overflow-hidden border w-16 h-16 shrink-0"
                        :class="isDark ? 'border-[#3a3a50]' : 'border-gray-200'">
                        <template x-if="f.type.startsWith('image')">
                            <img :src="f.preview" class="w-full h-full object-cover">
                        </template>
                        <template x-if="f.type.startsWith('video')">
                            <div class="w-full h-full bg-gray-800 flex items-center justify-center">
                                <i data-lucide="film" class="w-5 h-5 text-white"></i>
                            </div>
                        </template>
                        <template x-if="!f.type.startsWith('image') && !f.type.startsWith('video')">
                            <div class="w-full h-full flex items-center justify-center"
                                :class="isDark ? 'bg-[#21212e]' : 'bg-gray-100'">
                                <i data-lucide="file" class="w-5 h-5 text-orange-500"></i>
                            </div>
                        </template>
                        <div class="absolute bottom-0 left-0 right-0 px-1 pb-0.5 text-center"
                            style="font-size:9px;background:rgba(0,0,0,0.5);color:white;"
                            x-text="f.name.length > 8 ? f.name.substring(0,8)+'...' : f.name"></div>
                        <button @click="files.splice(i,1)"
                            class="absolute top-0.5 right-0.5 w-4 h-4 bg-red-500 text-white rounded-full text-xs leading-none opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">×</button>
                    </div>
                </template>
            </div>
        </div>

        {{-- Input --}}
        @if($activeContact)
            {{-- @if($isBlocked && !$isArtist)
            <div class="p-4 shrink-0">
                <div class="blocked-banner">🚫 Kamu tidak dapat mengirim pesan. Kamu diblokir oleh artist.</div>
            </div> --}}
            @if($isBlocked && !$isArtist)
<div class="p-4 shrink-0">
    <div class="rounded-2xl overflow-hidden border"
        :class="isDark ? 'border-red-500/20 bg-red-500/5' : 'border-red-200 bg-red-50'">
        <div class="flex flex-col items-center justify-center gap-3 py-6 px-4 text-center">
            <div class="w-14 h-14 rounded-full flex items-center justify-center"
                style="background:rgba(239,68,68,0.12); border: 2px solid rgba(239,68,68,0.3);">
                <i data-lucide="shield-off" style="width:28px;height:28px;color:#ef4444;"></i>
            </div>
            <div>
                <p class="font-bold text-sm text-red-500">Akun Kamu Terblokir</p>
                <p class="text-xs mt-1" :class="isDark ? 'text-gray-400' : 'text-gray-500'">
                    Kamu tidak dapat mengirim pesan kepada artist ini.
                </p>
            </div>
        </div>
    </div>
</div>
            @else
            <div class="p-4 shrink-0">
                <input type="file" id="upfile" multiple
                    accept="image/*,video/*,.pdf,.zip,.psd,.ai,.sketch,.fig"
                    class="hidden" @change="onFile($event)">

                <div class="input-box flex items-end gap-2 px-4 py-3">
                    <div class="flex gap-1 shrink-0">
                        <button @click="document.getElementById('upfile').click()"
                            class="w-8 h-8 rounded-lg flex items-center justify-center transition-all"
                            :class="isDark ? 'text-gray-400 hover:text-orange-400 hover:bg-[#21212e]' : 'text-gray-400 hover:text-[#21212e] hover:bg-gray-100'">
                            <i data-lucide="paperclip" class="w-4 h-4"></i>
                        </button>
                        <button @click="document.getElementById('upfile').click()"
                            class="w-8 h-8 rounded-lg flex items-center justify-center transition-all"
                            :class="isDark ? 'text-gray-400 hover:text-orange-400 hover:bg-[#21212e]' : 'text-gray-400 hover:text-[#21212e] hover:bg-gray-100'">
                            <i data-lucide="image-plus" class="w-4 h-4"></i>
                        </button>
                    </div>

                    <textarea x-model="msg"
                        @keydown.enter.exact.prevent="send()"
                        @keydown.shift.enter.stop
                        placeholder="Ketik pesan... (Enter untuk kirim)"
                        rows="1"
                        class="flex-1 bg-transparent outline-none resize-none text-sm leading-relaxed"
                        style="max-height:100px;overflow-y:auto;"
                        :class="isDark ? 'text-white placeholder-gray-600' : 'text-[#21212e] placeholder-gray-400'"
                    ></textarea>

                    <button @click="send()" :disabled="sending"
                        class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 transition-all"
                        :class="(msg.trim() || files.length) && !sending
                            ? 'bg-orange-500 text-white hover:bg-orange-600'
                            : (isDark ? 'bg-[#21212e] text-gray-600' : 'bg-gray-100 text-gray-400')">
                        <i data-lucide="send" class="w-4 h-4" x-show="!sending"></i>
                        <i data-lucide="loader" class="w-4 h-4 animate-spin" x-show="sending"></i>
                    </button>
                </div>
                <p class="text-xs mt-1.5 px-2" :class="isDark ? 'text-gray-600' : 'text-gray-400'">
                    <kbd class="px-1.5 py-0.5 rounded" :class="isDark ? 'bg-[#21212e]' : 'bg-gray-100'">Enter</kbd> kirim &nbsp;
                    <kbd class="px-1.5 py-0.5 rounded" :class="isDark ? 'bg-[#21212e]' : 'bg-gray-100'">Shift+Enter</kbd> baris baru &nbsp;
                    Max 50MB | PNG JPG MP4 PSD PDF ZIP
                </p>
            </div>
            @endif
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
const RECEIVER_ID  = {{ $receiverId ?? 'null' }};
const MY_ID        = {{ session('user_id') ?? 'null' }};
const MY_NAME      = @json(session('user_name'));
const IS_ARTIST    = {{ $isArtist ? 'true' : 'false' }};
const CONTACT_NAME = @json($activeContact->name ?? '');
const CSRF         = document.querySelector('meta[name=csrf-token]')?.content;

// Set untuk track ID pesan yang sudah di-render — cegah duplikat
const renderedIds = new Set();

// Init renderedIds dari pesan yang sudah ada di DOM
document.querySelectorAll('[id^="msg-"]').forEach(el => {
    const id = parseInt(el.id.replace('msg-', ''));
    if (!isNaN(id)) renderedIds.add(id);
});

async function deleteMsg(msgId) {
    if (!confirm('Hapus pesan ini?')) return;
    const res = await fetch('{{ route('chat.delete') }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json' },
        body: JSON.stringify({ message_id: msgId }),
    });
    const data = await res.json();
    if (data.success) {
        const el = document.getElementById('msg-' + msgId);
        if (el) {
            el.style.transition = 'all 0.25s';
            el.style.opacity    = '0';
            el.style.transform  = 'scale(0.95)';
            setTimeout(() => {
                el.remove();
                renderedIds.delete(msgId); // ← hapus dari Set agar tidak conflict
            }, 250);
        }
    }
}

function buildMsgHtml(m, isDark) {
    const myAvatar    = `https://ui-avatars.com/api/?name=${encodeURIComponent(MY_NAME)}&background=7c3aed&color=fff`;
    const theirAvatar = IS_ARTIST
        ? `https://ui-avatars.com/api/?name=${encodeURIComponent(CONTACT_NAME)}&background=7c3aed&color=fff`
        : `https://ui-avatars.com/api/?name=Admin+Artist&background=f97316&color=fff`;
    const avatar = m.mine ? myAvatar : theirAvatar;

    let content = '';
    if (m.file_path) {
        if (m.file_type && m.file_type.startsWith('image')) {
            content += `<img src="${m.file_path}" class="max-w-xs rounded-xl object-cover max-h-48 mb-1 cursor-pointer" onclick="window.open(this.src)">`;
        } else if (m.file_type && m.file_type.startsWith('video')) {
            content += `<video src="${m.file_path}" controls class="max-w-xs rounded-xl max-h-48 mb-1"></video>`;
        } else {
            content += `<a href="${m.file_path}" target="_blank" class="file-badge mb-1">
                <i data-lucide="file" style="width:14px;height:14px;flex-shrink:0;"></i>
                <span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${m.file_name ?? 'File'}</span>
            </a>`;
        }
    }
    if (m.message) {
// HAPUS isDark dari sini — biarkan CSS yang handle
const bubbleClass = m.mine
    ? 'bubble-mine px-4 py-2.5 text-sm'
    : 'bubble-artist px-4 py-2.5 text-sm';
            content += `<div class="${bubbleClass}">${m.message}</div>`;
    }

    const timeColor = isDark ? 'color:#4b5563' : 'color:#9ca3af';

// Artist bisa hapus semua, user hanya pesan miliknya (mine = true)
const deleteBtn = (IS_ARTIST || m.mine)
    ? `<button class="msg-delete-btn" onclick="deleteMsg(${m.id})" title="Hapus">
           <i data-lucide="trash-2" style="width:13px;height:13px;"></i>
       </button>`
    : '';

    const div = document.createElement('div');
    div.id        = `msg-${m.id}`;
    div.className = `msg-row ${m.mine ? 'mine' : ''}`;
    div.innerHTML = `
        <img src="${avatar}" class="w-8 h-8 rounded-full object-cover" style="flex-shrink:0;">
        <div class="max-w-xs lg:max-w-md flex flex-col ${m.mine ? 'items-end' : 'items-start'}">
            ${content}
            <span class="text-xs mt-1 px-1" style="${timeColor}">${m.time}</span>
        </div>
        ${deleteBtn}
    `;
    return div;
}

function chatApp() {
    return {
        isDark: false,
        query: '',
        msg: '',
        files: [],
        sending: false,
        lastMsgId: 0,

        openSidebar() {
            document.getElementById('sidebar').classList.add('open');
            document.getElementById('overlay').classList.add('show');
        },
        closeSidebar() {
            document.getElementById('sidebar').classList.remove('open');
            document.getElementById('overlay').classList.remove('show');
        },

        onFile(e) {
            Array.from(e.target.files).forEach(file => {
                const r = new FileReader();
                r.onload = ev => this.files.push({
                    file,
                    type: file.type,
                    preview: ev.target.result,
                    name: file.name,
                });
                r.readAsDataURL(file);
            });
            e.target.value = '';
            this.$nextTick(() => lucide.createIcons());
        },

        async send() {
            if (this.sending) return;
            if (!this.msg.trim() && !this.files.length) return;
            if (!RECEIVER_ID) return;

            this.sending = true;

            const doSend = async (fd) => {
                const res = await fetch('{{ route('chat.send') }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': CSRF },
                    body: fd,
                });
                return res.json();
            };

            try {
                // Snapshot files & msg sebelum reset
                const filesToSend = [...this.files];
                const textToSend  = this.msg.trim();

                // Reset lebih awal agar tidak bisa kirim dobel
                this.files = [];
                this.msg   = '';

                // Kirim file satu per satu (sequential)
                for (const f of filesToSend) {
                    const fd = new FormData();
                    fd.append('receiver_id', RECEIVER_ID);
                    fd.append('file', f.file);
                    const data = await doSend(fd);
                    if (data.error) {
                        alert(data.error);
                    } else if (!renderedIds.has(data.id)) {
                        renderedIds.add(data.id);
                        if (data.id > this.lastMsgId) this.lastMsgId = data.id;
                        const el = buildMsgHtml(data, this.isDark);
                        document.getElementById('new-msgs').appendChild(el);
                        lucide.createIcons();
                    }
                }

                // Kirim teks
                if (textToSend) {
                    const fd = new FormData();
                    fd.append('receiver_id', RECEIVER_ID);
                    fd.append('message', textToSend);
                    const data = await doSend(fd);
                    if (data.error) {
                        alert(data.error);
                    } else if (!renderedIds.has(data.id)) {
                        renderedIds.add(data.id);
                        if (data.id > this.lastMsgId) this.lastMsgId = data.id;
                        const el = buildMsgHtml(data, this.isDark);
                        document.getElementById('new-msgs').appendChild(el);
                        lucide.createIcons();
                    }
                }

                this.scrollDown();
            } catch (err) {
                console.error(err);
            } finally {
                this.sending = false;
            }
        },

    async poll() {
    if (!RECEIVER_ID || !MY_ID) return;
    try {
        const res  = await fetch(`{{ route('chat.fetch') }}?contact_id=${RECEIVER_ID}&last_id=${this.lastMsgId}`);
        const msgs = await res.json();

        // ✅ Cek pesan yang sudah dihapus — bandingkan DOM vs server
        const serverIds = new Set(msgs.map(m => m.id));
        renderedIds.forEach(id => {
            if (!serverIds.has(id)) {
                // Pesan ada di DOM tapi sudah tidak ada di server → hapus
                const el = document.getElementById('msg-' + id);
                if (el) {
                    el.style.transition = 'all 0.25s';
                    el.style.opacity    = '0';
                    el.style.transform  = 'scale(0.95)';
                    setTimeout(() => el.remove(), 250);
                }
                renderedIds.delete(id);
            }
        });

        // ✅ Tambah pesan baru yang belum ada di DOM
        let hasNew = false;
        msgs.forEach(m => {
            if (!renderedIds.has(m.id)) {
                renderedIds.add(m.id);
                if (m.id > this.lastMsgId) this.lastMsgId = m.id;
                const el = buildMsgHtml(m, this.isDark);
                document.getElementById('new-msgs').appendChild(el);
                hasNew = true;
            }
        });

        if (hasNew) {
            lucide.createIcons();
            this.scrollDown();
        }
    } catch (e) {}
},

        scrollDown() {
            this.$nextTick(() => {
                const el = document.getElementById('msgs');
                if (el) el.scrollTop = el.scrollHeight;
            });
        },

        async toggleBlock(userId) {
            const res = await fetch('{{ route('chat.block') }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json' },
                body: JSON.stringify({ user_id: userId }),
            });
            const data = await res.json();
            if (data.success) {
                alert(data.message);
                window.location.reload();
            }
        },

        init() {
            this.isDark = document.documentElement.classList.contains('dark');
            new MutationObserver(() => {
                this.isDark = document.documentElement.classList.contains('dark');
            }).observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });

            this.lastMsgId = {{ $messages->isNotEmpty() ? $messages->last()->id : 0 }};
            this.scrollDown();

            if (RECEIVER_ID) {
                setInterval(() => this.poll(), 3000);
            }

            lucide.createIcons();
        }
    }
}
</script>
@endpush