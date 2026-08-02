<div class="discount-category-tree__node" x-data="{ open: false }">
    <div class="discount-category-tree__row" style="padding-left: {{ 0.75 + ($level * 1.25) }}rem">
        <label class="discount-category-tree__label {{ $level ? 'discount-category-tree__label--child' : '' }}">
            <input
                class="discount-category-tree__radio"
                type="radio"
                name="{{ $statePath }}"
                value="{{ $category['id'] }}"
                wire:model="{{ $statePath }}"
            >
            <span>{{ $category['name'] }}</span>
        </label>

        @if ($category['children'])
            <button
                class="discount-category-tree__toggle"
                type="button"
                aria-label="Показати підрозділи {{ $category['name'] }}"
                x-bind:aria-expanded="open"
                x-on:click="open = ! open"
            >
                <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M7.21 4.23a.75.75 0 0 1 1.06-.02l5.25 5a.75.75 0 0 1 0 1.08l-5.25 5a.75.75 0 1 1-1.04-1.08L11.91 10 7.23 5.31a.75.75 0 0 1-.02-1.08Z" clip-rule="evenodd" />
                </svg>
            </button>
        @endif
    </div>

    @if ($category['children'])
        <div class="discount-category-tree__children" x-show="open" x-collapse>
            @foreach ($category['children'] as $child)
                @include('filament.forms.components.category-tree-node', [
                    'category' => $child,
                    'level' => $level + 1,
                    'statePath' => $statePath,
                ])
            @endforeach
        </div>
    @endif
</div>
