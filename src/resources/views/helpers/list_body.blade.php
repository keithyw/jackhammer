<div class="panel panel-inverse">
    <div class="panel-heading">
        <div class="btn-group pull-right">
            <?php $base = isset($prefix) ? "{$prefix}.{$type}" : $type; ?>
            <?php $base = str_replace('_', '-', $base); ?>
            <?php $del = isset($prefix) ? "{$prefix}/{$type}" : $type; ?>
            <?php $title = str_singular($type); ?>
            <a class="btn btn-primary" href="{{ route("{$base}.create") }}">@lang('messages.button_create')</a>
        </div>
        <h3 class="panel-title">@lang("messages.{$title}_index_title")</h3>
    </div>
    <div class="panel-body">
        <div class="table-responsive">

            <table id="data-table" class="table table-striped table-bordered">
                <tbody>
                @foreach ($list as $row)
                <tr>
                    <td width="70%"><a href="{{ route("{$base}.show", ['id' => $row->id]) }}">{{ $row->{$linkTextField} }}</a></td>
                    <td width="15%">
                        <a class="btn btn-primary" href="{{ route("{$base}.edit", ['id' => $row->id]) }}">@lang('messages.button_edit')</a>
                    </td>
                    <td width="15%">
                        <?php $del = str_replace('_', '-', $del); ?>
                        {!! Form::open(['url' => "{$del}/{$row->id}", 'method' => 'DELETE']) !!}
                        {!! Form::submit(Lang::get('messages.button_delete'), ['class' => 'btn btn-primary']) !!}
                        {!! Form::close() !!}
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
            @if (isset($prefix))
                <?php $path = str_replace('_', '-', $type); ?>
                <?php $list->setPath($path); ?>
            @endif
            {!! $list->render() !!}
        </div>
    </div>
</div>