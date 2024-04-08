<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-row w-full justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ Auth::user()->hasRole('owner') ? __('Store Orders') : __('My Transactions') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white flex flex-col gap-y-5 overflow-hidden p-10 shadow-sm sm:rounded-lg">
                @forelse ($product_transactions as $transaction)
                    <div class="item-card flex flex-row justify-between items-center">
                        <a href="{{ route('product_transactions.show', $transaction) }}">
                            <div class="flex flex-row items-center gap-x-3">
                                <div>
                                    <p class="text-base text-slate-500">
                                        Total Transaction
                                    </p>
                                    <h3 class="text-2x font-bold text-indigo-950">
                                        Rp. {{ $transaction->total_amount }}
                                    </h3>
                                </div>
                            </div>
                        </a>
                        <div class="hidden md:flex flex-col">
                            <p class="text-base text-slate-500">
                                Date
                            </p>
                            <h3 class="text-2x font-bold text-indigo-950">
                                {{ $transaction->created_at }}
                            </h3>
                        </div>
                        @if ($transaction->is_paid)
                            <span class="px-3 py-1 rounded-full text-white bg-green-500">
                                <p class="text-white font-bold text-sm">SUCCESS</p>
                            </span>
                        @else
                            <span class="px-3 py-1 rounded-full text-white bg-orange-500">
                                <p class="text-white font-bold text-sm">PENDING</p>
                            </span>
                        @endif

                        <div class="hidden md:flex flex-row items-center gap-x-3">
                            <a href="{{ route('product_transactions.show', $transaction) }}"
                                class="py-3 px-5 rounded-full text-white bg-indigo-700">View Detail</a>
                        </div>
                    </div>
                    <hr class="my-3">
                @empty
                    <p>Belum ada transaksi yang tersedia.</p>
                @endforelse


            </div>

        </div>
    </div>
</x-app-layout>
