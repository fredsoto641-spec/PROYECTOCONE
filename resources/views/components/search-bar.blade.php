@props([
    'cities' => [],
    'categories' => [],
    'labels',
    'selected' => [],
])

<form action="{{ $labels['action'] }}" method="GET" class="mx-auto grid w-full max-w-5xl gap-3 rounded-[1.75rem] bg-white p-3 shadow-2xl shadow-black/20 md:grid-cols-[1fr_1fr_1.4fr_auto]" aria-label="{{ $labels['formLabel'] }}">
    <label class="sr-only" for="search-location">{{ $labels['cityLabel'] }}</label>
    <select id="search-location" name="location" class="h-12 rounded-full border-[#E5E7EB] bg-[#F8F8F8] px-4 text-sm text-[#222222] focus:border-[#E91E63] focus:ring-[#E91E63]">
        <option value="">{{ $labels['cityPlaceholder'] }}</option>
        @foreach ($cities as $city)
            @php
                $cityLabel = is_array($city) ? $city['label'] : $city;
                $cityValue = is_array($city) ? $city['value'] : Str::slug($city);
            @endphp
            <option value="{{ $cityValue }}" @selected(($selected['location'] ?? '') === $cityValue)>{{ $cityLabel }}</option>
        @endforeach
    </select>

    <label class="sr-only" for="search-category">{{ $labels['categoryLabel'] }}</label>
    <select id="search-category" name="category" class="h-12 rounded-full border-[#E5E7EB] bg-[#F8F8F8] px-4 text-sm text-[#222222] focus:border-[#E91E63] focus:ring-[#E91E63]">
        <option value="">{{ $labels['categoryPlaceholder'] }}</option>
        @foreach ($categories as $category)
            @php
                $categoryLabel = is_array($category) ? $category['label'] : $category;
                $categoryValue = is_array($category) ? $category['value'] : Str::slug($category);
            @endphp
            <option value="{{ $categoryValue }}" @selected(($selected['category'] ?? '') === $categoryValue)>{{ $categoryLabel }}</option>
        @endforeach
    </select>

    <label class="sr-only" for="search-query">{{ $labels['queryLabel'] }}</label>
    <input id="search-query" name="query" type="search" value="{{ $selected['query'] ?? '' }}" placeholder="{{ $labels['queryPlaceholder'] }}" class="h-12 rounded-full border-[#E5E7EB] bg-[#F8F8F8] px-4 text-sm text-[#222222] placeholder:text-[#6B7280] focus:border-[#E91E63] focus:ring-[#E91E63]">

    <x-button type="submit" size="lg" class="h-12 px-8">{{ $labels['submitLabel'] }}</x-button>
</form>
