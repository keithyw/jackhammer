<div class="panel panel-inverse">
    <div class="panel-heading">
        <?php $create = isset($prefix) ? "{$prefix}/{$type}" : $type; ?>
        <h1 class="panel-title">@lang("messages.{$type}_create")</h1>
    </div>
    <div class="panel-body">
        {!! Form::open(array('url' => str_plural(str_replace('_', '-', $create)), 'class' => 'form-horizontal')) !!}
        @include("{$type}.form")
        {!! Form::close() !!}
    </div>
</div>