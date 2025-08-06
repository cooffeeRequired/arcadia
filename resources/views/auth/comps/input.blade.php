@php
    $type = $type ?? 'text';
    $name = $name ?? '';
    $label = $label ?? null;
    $placeholder = $placeholder ?? null;
    $value = $value ?? null;
    $required = $required ?? false;
    $disabled = $disabled ?? false;
    $autocomplete = $autocomplete ?? null;
    $class = $class ?? '';
    $error = $error ?? null;
    
    $inputId = $name . '_' . uniqid();
    $hasError = $error || (isset($errors) && $errors->has($name));
    $validationState = $hasError ? 'error' : ((!empty($_POST[$name])) ? 'success' : 'neutral');
    
    $baseClasses = 'block w-full px-4 py-3 text-gray-900 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200';

$inputClasses = match ($validationState) {
    'error' => $baseClasses . ' border-red-500 bg-red-50',
    'success' => $baseClasses . ' border-green-500 bg-green-50',
    default => $baseClasses . ' border-gray-300 bg-white hover:border-gray-400',
};
    
    if ($disabled) {
        $inputClasses .= ' bg-gray-100 cursor-not-allowed';
    }
    
    $inputClasses .= ' ' . $class;
@endphp

<div class="space-y-2">
    @if($label)
        <label for="{{ $inputId }}" class="block text-sm font-medium text-gray-700">
            {{ $label }}
            @if($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif

    <div class="relative">
        <input
            type="{{ $type }}"
            id="{{ $inputId }}"
            name="{{ $name }}"
            value="{{ old($name, $value) }}"
            placeholder="{{ $placeholder }}"
            @if($required) required @endif
            @if($disabled) disabled @endif
            @if($autocomplete) autocomplete="{{ $autocomplete }}" @endif
            class="{{ $inputClasses }}"
            {{ $attributes }}
        />

        @if($validationState !== 'neutral')
            <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                @if($validationState === 'error')
                    <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                @elseif($validationState === 'success')
                    <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                    </svg>
                @endif
            </div>
        @endif
    </div>

    @if($hasError)
        <p class="text-sm text-red-600 flex items-center gap-1">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
            </svg>
            {{ $error ?? $errors->first($name) }}
        </p>
    @endif
</div>