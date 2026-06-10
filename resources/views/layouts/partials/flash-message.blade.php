@if (session('success') || session('success_title') || session('success_message'))
    <div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-emerald-800 shadow-sm">
        <div class="flex items-start gap-3">
            <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700">
                <span class="material-symbols-outlined text-[20px]">check_circle</span>
            </div>

            <div>
                <p class="text-sm font-bold">
                    {{ session('success_title', 'Berhasil') }}
                </p>

                <p class="mt-1 text-sm leading-6">
                    {{ session('success_message', session('success')) }}
                </p>

                @if(session('success_detail'))
                    <p class="mt-1 text-xs leading-5 text-emerald-700">
                        {{ session('success_detail') }}
                    </p>
                @endif
            </div>
        </div>
    </div>
@endif

@if (session('error') || session('error_title') || session('error_message'))
    <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 p-4 text-red-800 shadow-sm">
        <div class="flex items-start gap-3">
            <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-red-100 text-red-700">
                <span class="material-symbols-outlined text-[20px]">error</span>
            </div>

            <div>
                <p class="text-sm font-bold">
                    {{ session('error_title', 'Gagal') }}
                </p>

                <p class="mt-1 text-sm leading-6">
                    {{ session('error_message', session('error')) }}
                </p>
            </div>
        </div>
    </div>
@endif

