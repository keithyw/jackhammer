{!! $header !!}

return [
@foreach ($items as $name => $info)
    '{{ $name }}' => [
        'time' => '{{ $info['time'] }}',
        'tag' => '{{ $info['tag'] }}'
    ],
@endforeach
];