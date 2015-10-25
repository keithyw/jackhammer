<div class="panel panel-inverse">
    <div class="panel-heading">
        <h1 class="panel-title">@lang("messages.{$type}_edit")</h1>
    </div>
    <div class="panel-body">
        <?php $base = isset($prefix) ? "{$prefix}/{$type}" : $type; ?>
        <?php $url = str_plural(str_replace('_', '-', $base)) ?>
        {!! Form::open(array('url' => "{$url}/{$model->id}", 'class' => 'form-horizontal', 'method' => 'PUT')) !!}
        @include("{$type}.form")
        {!! Form::close() !!}
    </div>
</div>
