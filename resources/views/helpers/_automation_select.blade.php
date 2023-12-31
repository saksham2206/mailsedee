<select name="{{ $name }}"
	{{ isset($disabled) && $disabled == true ? ' disabled="disabled"' : "" }}
	@if(isset($placeholder))
		data-placeholder="{{ $placeholder }}"
	@endif
		class="select select-search{{ $classes }} {{ isset($class) ? $class : "" }}
			{{ isset($required) && !empty($required) ? 'required' : '' }}"
			{{ isset($multiple) && $multiple == true ? "multiple='multiple'" : "" }}
			{{ isset($readonly) && $readonly == true ? "readonly='readonly'" : "" }}
		>

	@if (isset($include_blank))
	
		<option value="">{{ $include_blank }}</option>
	@endif
	@if (isset($addExtras))

		<!-- <option value="">{{ $addExtras }}</option> -->
	@endif
		@php
	//dd($value,$options);
	@endphp	
	@foreach($options as $option)
		<option
			@if (is_array($value))
				{{ in_array($option['value'], $value) ? " selected" : "" }}
			@else
				{{ in_array($option['value'], explode(",", $value)) ? " selected" : "" }}
			@endif
			value="{{ $option['value'] }}"
		>{{ htmlspecialchars($option['text']) }}</option>
	@endforeach
</select>
