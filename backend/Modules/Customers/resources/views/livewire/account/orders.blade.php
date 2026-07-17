<div>
    <h1 class="text-3xl font-bold tracking-tight text-gray-900">Comenzile mele</h1>

    {{-- Lista reală de comenzi e furnizată de modulul Orders (Partea 9),
         încorporată prin NUME de componentă — Customers nu importă nicio clasă
         din Orders. Ghidat de existența rutei Orders (același tipar Route::has
         ca la SearchBox/MiniCart/auth), astfel încât pagina rămâne funcțională
         și fără modulul Orders instalat. --}}
    @if (\Illuminate\Support\Facades\Route::has('storefront.account.order'))
        @livewire('orders.account-orders')
    @else
        <div class="mt-8 rounded-2xl border border-dashed border-gray-300 bg-white px-6 py-16 text-center">
            <p class="text-gray-500">Vei vedea comenzile aici.</p>
            <p class="mt-1 text-sm text-gray-400">Modulul Orders (Partea 9) leagă fiecare comandă de acest cont.</p>
        </div>
    @endif
</div>
