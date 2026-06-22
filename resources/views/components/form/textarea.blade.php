@props(['name', 'label' => null, 'value' => null, 'rows' => 3, 'required' => false])
<div>
    @if ($label)
        <label for="{{ $name }}" class="label">{{ $label }} @if($required)<span class="text-red-500">*</span>@endif</label>
    @endif
    <textarea name="{{ $name }}" id="{{ $name }}" rows="{{ $rows }}" {{ $required ? 'required' : '' }}
              {{ $attributes->merge(['class' => 'input']) }}>{{ old($name, $value) }}</textarea>
    @error($name)<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
</div>
