{!! $header !!}

namespace App\Models;

use Conark\Jackhammer\Models\BaseModel;
@if ($isUser)
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
@endif

class {{ studly_case(str_singular($tableName)) }} extends BaseModel <?php if($isUser){ ?> implements AuthenticatableContract, AuthorizableContract, CanResetPasswordContract <?php } ?>
{
@if ($isUser)
    use Authenticatable, Authorizable, CanResetPassword;
@endif

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
