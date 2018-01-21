{!! $header !!}

namespace App\Repositories;

use Jackhammer\Repositories\BaseRepository;
use App\{{ $modelPath }}\{{ $model }};

class {{ $className }} extends BaseRepository implements {{ $interface }}
{
    public function __construct({{ $model }} $model){
        parent::__construct($model);
    }
}