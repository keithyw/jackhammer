{!! $header !!}

namespace App\Models;

use Conark\Jackhammer\Models\BaseModel;

class {{ studly_case(str_singular($tableName)) }} extends BaseModel
{
    protected $table = '{{ $tableName }}';

    protected $fillable = [{!! join(',', $fillable) !!}];

    protected $hidden = [{!! join(',', $hidden) !!}];

    protected $rules = [
@foreach ($rules as $field => $r)
        '{{ $field }}' => '{{ $r }}',
@endforeach
    ];

@foreach ($relations as $r)
    public function {{ $r['method'] }}(){
        return $this->belongsTo('{{$r['belongsTo']}}');
    }

@endforeach
@foreach ($references as $ref)
    public function {{ $ref['method'] }}(){
@if ($ref['hasType'] == 'one')
        return $this->hasOne('{{$ref['model']}}');
@else
        return $this->hasMany('{{$ref['model']}}');
@endif
    }

@endforeach
}
