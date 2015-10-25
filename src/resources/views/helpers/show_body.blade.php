<div class="panel panel-inverse">
    <div class="panel-heading">
        <?php $index = str_singular($type); ?>
        <h3 class="panel-title">@lang("messages.{$index}_index_title")</h3>
    </div>
    <div class="panel-body">
        @include($details)
    </div>
    @if (!isset($flag))
    <div class="panel-footer">
        <div class="col-sm-4">
            <?php $base = isset($prefix) ? "{$prefix}.{$type}" : $type; ?>
            <?php $url = str_replace('_', '-', $base); ?>
            <a class="btn btn-primary" href="{{ route("{$url}.edit", ['id' => $model->id]) }}">@lang('messages.button_edit')</a>
        </div>
        <div class="col-sm-4">
            <?php $del = isset($prefix) ? "{$prefix}/{$type}" : $type; ?>
            <?php $del = str_replace('_', '-', $del); ?>
            {!! Form::open(['url' => "{$del}/{$model->id}", 'method' => 'DELETE']) !!}
            {!! Form::submit(Lang::get('messages.button_delete'), ['class' => 'btn btn-primary']) !!}
            {!! Form::close() !!}
        </div>
        <div class="clearfix"></div>
    </div>
    @endif
</div>

@if (isset($hooks))
    @foreach ($hooks as $hook)
        <div id="{{$hook}}"></div>
    @endforeach
@endif