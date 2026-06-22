@props(['name', 'label' => null, 'options' => [], 'selected' => null, 'required' => false, 'placeholder' => '—'])
<div>
    @if ($label)
        <label for="{{ $name }}" class="label">{{ $label }} @if($required)<span class="text-red-500">*</span>@endif</label>
    @endif
    <select name="{{ $name }}" id="{{ $name }}" {{ $required ? 'required' : '' }}
            {{ $attributes->merge(['class' => 'input']) }}>
        <option value="">{{ $placeholder }}</option>
        @foreach ($options as $key => $text)
            <option value="{{ $key }}" {{ (string) old($name, $selected) === (string) $key ? 'selected' : '' }}>{{ $text }}</option>
        @endforeach
    </select>
    @error($name)<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
</div>
