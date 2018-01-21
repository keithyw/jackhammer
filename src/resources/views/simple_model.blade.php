{!! $header !!}

namespace App\Models;

use Jackhammer\Models\BaseModel;

class {{ studly_case(str_singular($tableName)) }} extends BaseModel
{

    protected $table = '{{ $tableName }}';

    protected $fillable = [];

    protected $rules = [];
}
