{!! $header !!}

return [
@foreach ($messages as $k => $v)
    '{{ $k }}' => '{{ $v }}',
@endforeach
];