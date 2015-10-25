<div class="form-group">
    {!! Form::label($field, Lang::get("messages.form_field_{$field}"), ['placeholder' => Lang::get("messages.form.form_field_{$field}"), 'class' => 'col-sm-2 control-label']) !!}
    <div class="col-sm-4">
        @if (isset($namespace))
            {!! Form::password("{$namespace}[{$field}]", ['class' => 'form-control', isset($required) ? 'required' : null]) !!}
        @else
            {!! Form::password($field, ['class' => 'form-control', isset($required) ? 'required' : null]) !!}
        @endif
        @if ($errors->has($field))
            <p class="bg-danger">
                {{ $errors->first($field) }}
            </p>
        @endif
    </div>
</div>