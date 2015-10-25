<div class="form-group">
    {!! Form::label($name, Lang::get("messages.form_field_{$name}"), ['class' => 'col-sm-2 control-label']) !!}
    <div class="col-sm-4">
        {!! Form::select($name, $items->lists($display, 'id'), $selected) !!}
        @if ($errors->has($name))
            <p class="bg-danger">
                {{ $errors->first($name) }}
            </p>
        @endif
    </div>
</div>