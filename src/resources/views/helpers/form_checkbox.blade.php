<div class="form-group">
    <?php $messageFile = isset($messageFile) ? $messageFile : 'messages'; ?>
    {!! Form::label($field, Lang::get("{$messageFile}.form_field_{$field}"), ['class' => 'col-sm-2 control-label']) !!}
    <?php $checked = $model->$field ? true : false ?>
    <div class="col-sm-4">
        <label class="checkbox-inline" style="padding-top:0">
        @if (isset($namespace))
            {!! Form::checkbox("{$namespace}[{$field}]", 1, $checked, ['style' => 'margin-top:0', isset($required) ? 'required' : null]) !!}
        @else
            {!! Form::checkbox($field, 1, $checked, ['style' => 'margin-top:0', isset($required) ? 'required' : null]) !!}
        @endif
        </label>
    </div>
</div>