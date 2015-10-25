<div class="row">
    <div class="col-sm-4">
        @lang("messages.form_field_{$field}")
    </div>
    <div class="col-sm-4">
        @if (isset($route))
            <a href="{{ route($route, $params) }}">{{ $model->$field }}</a>
        @else
            {{ $model->$field }}
        @endif
    </div>
</div>