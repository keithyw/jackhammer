{!! $header !!}

namespace App\Repositories;

use Jackhammer\Repositories\BaseRepository;
use App\{{ $modelPath }}\{{ $model }};
use App\{{ $repositoryContractPath }}\{{ $interface }} as {{ $interface }}Interface;

class {{ $className }} extends BaseRepository implements {{ $interface }}Interface
{
    public function __construct({{ $model }} $model){
        parent::__construct($model);
    }
}