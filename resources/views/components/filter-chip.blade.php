@props([
    'label',
    'active' => false,
])

<button type="button" @class([
    'rounded-full border px-4 py-2 text-sm font-semibold transition duration-200 focus:outline-none focus:ring-2 focus:ring-[#E91E63] focus:ring-offset-2',
    'border-[#E91E63] bg-[#E91E63] text-white shadow-sm shadow-[#E91E63]/25' => $active,
    'border-[#E5E7EB] bg-white text-[#6B7280] hover:border-[#E91E63]/40 hover:text-[#E91E63]' => ! $active,
]) aria-pressed="{{ $active ? 'true' : 'false' }}">
    {{ $label }}
</button>
