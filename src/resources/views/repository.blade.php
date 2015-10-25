{!! $header !!}

namespace App\Repositories;

use Conark\Jackhammer\RepositoryWrapper;
use App\{{ $modelPath }}\{{ $model }};

class {{ $className }} extends RepositoryWrapper implements {{ $interface }}
{
    public function __construct({{ $model }} $model){
        parent::__construct($model);
    }
}