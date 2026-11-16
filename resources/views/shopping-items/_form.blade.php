@php($item ??= null)

<flux:input name="name" :label="__('Name')" :value="old('name', $item?->name)" required autofocus />
<flux:input name="quantity" :label="__('Quantity')" :value="old('quantity', $item?->quantity)" />
<flux:input name="category" :label="__('Category')" :value="old('category', $item?->category)" />
