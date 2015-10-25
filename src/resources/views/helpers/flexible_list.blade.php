<div class="panel panel-inverse">
    <div class="panel-heading">
        <h3 class="panel-title">@lang("messages.{$type}_index_title")</h3>
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
                            {!! Form::open(['url' => "{$del}/{$row->id}", 'method' => 'DELETE']) !!}
                            {!! Form::submit(Lang::get('messages.button_delete'), ['class' => 'btn btn-primary']) !!}
                            {!! Form::close() !!}
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            @if (isset($prefix))
                <?php $list->setPath($type); ?>
            @endif
            {!! $list->render() !!}
        </div>
    </div>
</div>