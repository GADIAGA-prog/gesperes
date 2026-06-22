@props(['name', 'label' => null, 'type' => 'text', 'value' => null, 'required' => false])
<div>
    @if ($label)
        <label for="{{ $name }}" class="label">{{ $label }} @if($required)<span class="text-red-500">*</span>@endif</label>
    @endif
    <input type="{{ $type }}" name="{{ $name }}" id="{{ $name }}"
           value="{{ old($name, $value) }}" {{ $required ? 'required' : '' }}
           {{ $attributes->merge(['class' => 'input']) }}>
    @error($name)<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
</div>
