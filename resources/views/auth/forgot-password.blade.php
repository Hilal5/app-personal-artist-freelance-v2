@extends('layouts.app')
@section('title', 'Lupa Password — ArtSpace')

@section('content')
<div x-data="{ isDark: document.documentElement.classList.contains('dark') }" class="max-w-sm mx-auto mt-10">

    <div class="rounded-2xl p-7 border"
        :class="isDark ? 'bg-[#2a2a3d] border-[#3a3a50]' : 'bg-white border-gray-200'">

        {{-- Logo --}}
        <div class="flex items-center gap-2 mb-6">
            <div class="w-8 h-8 bg-orange-500 rounded-lg flex items-center justify-center">
                <i data-lucide="lock" class="w-4 h-4 text-white"></i>
            </div>
            <span class="font-bold text-lg" :class="isDark ? 'text-white' : 'text-[#21212e]'">Lupa Password</span>
        </div>

        <p class="text-xs mb-5" :class="isDark ? 'text-gray-400' : 'text-gray-500'">
            Masukkan email yang terdaftar. Kami akan mengirim link untuk reset password.
        </p>

        @if(session('success'))
        <div class="mb-4 p-3 rounded-xl bg-green-500/10 border border-green-500/30 text-green-400 text-xs font-bold">
            ✅ {{ session('success') }}
        </div>
        @endif

        @if($errors->any())
        <div class="mb-4 p-3 rounded-xl bg-red-500/10 border border-red-500/30 text-red-400 text-xs">
            @foreach($errors->all() as $err)<p>⚠️ {{ $err }}</p>@endforeach
        </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf
            <div class="mb-4">
                <label class="text-xs font-bold mb-1.5 block" :class="isDark ? 'text-gray-300' : 'text-[#21212e]'">Email</label>
                <div class="flex items-center gap-2 rounded-xl border px-3"
                    :class="isDark ? 'bg-[#21212e] border-[#3a3a50] focus-within:border-orange-500' : 'bg-gray-50 border-gray-200 focus-within:border-[#21212e]'">
                    <i data-lucide="mail" class="w-4 h-4 text-gray-400 shrink-0"></i>
                    <input type="email" name="email" value="{{ old('email') }}" required
                        placeholder="email@example.com"
                        class="bg-transparent py-2.5 text-sm outline-none w-full"
                        :class="isDark ? 'text-white placeholder-gray-600' : 'text-[#21212e] placeholder-gray-400'">
                </div>
            </div>

            <button type="submit"
                class="w-full py-2.5 rounded-xl bg-orange-500 text-white font-bold text-sm hover:bg-orange-600 transition-all">
                Kirim Link Reset
            </button>
        </form>

        <p class="text-center text-xs mt-4" :class="isDark ? 'text-gray-500' : 'text-gray-400'">
            Ingat password?
            <a href="{{ route('artist.profile') }}" class="text-orange-500 font-bold hover:underline">Kembali login</a>
        </p>
    </div>
</div>
@endsection

@push('scripts')
<script>lucide.createIcons();</script>
@endpush