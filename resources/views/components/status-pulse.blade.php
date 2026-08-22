@props(['status', 'size' => 'md'])

@php
    $statusMap = [
        'menunggu_konfirmasi' => [
            'label' => 'Menunggu Konfirmasi',
            'color' => 'bg-[#F2A93B]',
            'pulse' => 'pulse-amber',
            'badge' => 'bg-[#FEF9EE] text-[#F2A93B] border-[#F2A93B]/40',
            'icon' => 'clock',
        ],
        'diproses' => [
            'label' => 'Sedang Diproses',
            'color' => 'bg-[#F2A93B]',
            'pulse' => 'pulse-amber',
            'badge' => 'bg-[#FEF9EE] text-amber-800 border-amber-300',
            'icon' => 'utensils',
        ],
        'siap_diambil_dikirim' => [
            'label' => 'Siap Diambil / Dikirim',
            'color' => 'bg-[#0E9F6E]',
            'pulse' => 'pulse-green',
            'badge' => 'bg-[#EDFDF5] text-[#0B5A45] border-[#0E9F6E]/40',
            'icon' => 'package-check',
        ],
        'selesai' => [
            'label' => 'Selesai',
            'color' => 'bg-[#0E9F6E]',
            'pulse' => '',
            'badge' => 'bg-[#EDFDF5] text-[#0E9F6E] border-emerald-200',
            'icon' => 'check-circle-2',
        ],
        'dibatalkan' => [
            'label' => 'Dibatalkan',
            'color' => 'bg-[#E15554]',
            'pulse' => '',
            'badge' => 'bg-red-50 text-red-600 border-red-200',
            'icon' => 'x-circle',
        ],
        'retur_refund' => [
            'label' => 'Retur / Refund',
            'color' => 'bg-purple-600',
            'pulse' => '',
            'badge' => 'bg-purple-50 text-purple-700 border-purple-200',
            'icon' => 'rotate-ccw',
        ],
    ];

    $cfg = $statusMap[$status] ?? [
        'label' => ucfirst(str_replace('_', ' ', $status)),
        'color' => 'bg-gray-400',
        'pulse' => '',
        'badge' => 'bg-gray-100 text-gray-700 border-gray-200',
        'icon' => 'help-circle',
    ];
@endphp

<div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full border text-xs font-semibold {{ $cfg['badge'] }}">
    <span class="w-2 h-2 rounded-full {{ $cfg['color'] }} {{ $cfg['pulse'] }}"></span>
    <span>{{ $cfg['label'] }}</span>
</div>
