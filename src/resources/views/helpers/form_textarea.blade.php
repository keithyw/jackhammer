<div class="form-group">
    <?php $messageFile = isset($messageFile) ? $messageFile : 'messages'; ?>
    {!! Form::label($field, Lang::get("{$messageFile}.form_field_{$field}"), ['class' => 'col-sm-2 control-label']) !!}
    <div class="col-sm-4">
        @if (isset($namespace))
            {!! Form::text("{$namespace}[{$field}]", $model->$field, ['placeholder' => Lang::get("{$messageFile}.form_field_{$field}"), 'class' => 'form-control', isset($required) ? 'required' : null]) !!}
        @else
            {!! Form::textarea($field, $model->$field, ['placeholder' => Lang::get("{$messageFile}.form_field_{$field}"), 'class' => 'form-control', isset($required) ? 'required' : null]) !!}
        @endif
        @if (isset($namespace))
            @if ($errors->has("{$namespace}[{$field}]"))
                <p class="bg-danger">
                    {{ $errors->first("{$namespace}[{$field}]") }}
                </p>
            @endif
        @else
            @if ($errors->has($field))
                <p class="bg-danger">
                    {{ $errors->first($field) }}
                </p>
            @endif
        @endif

    </div>
</div>