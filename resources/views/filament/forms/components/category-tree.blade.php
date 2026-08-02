<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div class="discount-category-tree">
        @forelse ($categories as $category)
            @include('filament.forms.components.category-tree-node', [
                'category' => $category,
                'level' => 0,
                'statePath' => $getStatePath(),
            ])
        @empty
            <p class="discount-category-tree__empty">Категорій поки немає.</p>
        @endforelse
    </div>

    <style>
        .discount-category-tree {
            overflow: hidden;
            border: 1px solid rgb(209 213 219);
            border-radius: .75rem;
            background: white;
        }
        .dark .discount-category-tree {
            border-color: rgb(75 85 99);
            background: rgb(17 24 39);
        }
        .discount-category-tree__node + .discount-category-tree__node,
        .discount-category-tree__children > .discount-category-tree__node:first-child {
            border-top: 1px solid rgb(229 231 235);
        }
        .dark .discount-category-tree__node + .discount-category-tree__node,
        .dark .discount-category-tree__children > .discount-category-tree__node:first-child {
            border-color: rgb(55 65 81);
        }
        .discount-category-tree__row {
            display: flex;
            min-height: 3rem;
            align-items: center;
            gap: .65rem;
            padding: .5rem .75rem;
        }
        .discount-category-tree__label {
            display: flex;
            min-width: 0;
            flex: 1;
            cursor: pointer;
            align-items: center;
            gap: .65rem;
            font-weight: 600;
        }
        .discount-category-tree__label--child { font-weight: 400; }
        .discount-category-tree__radio {
            width: 1.1rem;
            height: 1.1rem;
            accent-color: rgb(217 119 6);
        }
        .discount-category-tree__toggle {
            display: grid;
            width: 2.25rem;
            height: 2.25rem;
            flex: none;
            place-items: center;
            border-radius: .5rem;
            color: rgb(75 85 99);
        }
        .discount-category-tree__toggle:hover { background: rgb(243 244 246); }
        .discount-category-tree__toggle svg {
            width: 1.15rem;
            height: 1.15rem;
            transition: transform .15s ease;
        }
        .discount-category-tree__toggle[aria-expanded="true"] svg { transform: rotate(90deg); }
        .discount-category-tree__children { background: rgb(249 250 251); }
        .dark .discount-category-tree__children { background: rgb(31 41 55); }
        .discount-category-tree__empty { padding: .75rem; color: rgb(107 114 128); }
    </style>
</x-dynamic-component>
