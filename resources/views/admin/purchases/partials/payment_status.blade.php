@if($status === 'paid')
    <span class="inline-flex items-center px-2 py-1 bg-green-100 text-green-700 text-[10px] font-bold rounded-full uppercase">
        Lunas
    </span>
@elseif($status === 'partial')
    <span class="inline-flex items-center px-2 py-1 bg-orange-100 text-orange-700 text-[10px] font-bold rounded-full uppercase">
        Sebagian
    </span>
@else
    <span class="inline-flex items-center px-2 py-1 bg-red-100 text-red-700 text-[10px] font-bold rounded-full uppercase">
        Hutang
    </span>
@endif
