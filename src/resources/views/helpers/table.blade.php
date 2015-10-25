<div class="table-responsive">
    <table id="data-table" class="table table-striped table-bordered">
        <thead>
            @foreach ($headers as $header)
                <th>{{ $header }}</th>
            @endforeach
        </thead>
        <tbody>
            @foreach ($list as $row)
            <tr>
                @foreach ($fields as $field)
                    @if ($field == 'subfield')
                        @foreach ($subfield['fields'] as $sub)
                            <td>
                                @if (isset($row->$subfield['model']))
                                    {{ $row->$subfield['model']->$sub }}
                                @else
                                    @lang('messages.bad_interest_id')
                                @endif
                            </td>
                        @endforeach
                    @else
                        <td>{{ $row->$field }}</td>
                    @endif
                @endforeach
            </tr>
            @endforeach
        </tbody>
    </table>
</div>