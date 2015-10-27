{!! $header !!}

namespace {{ $namespace }};

use Conark\Jackhammer\Http\Controllers\BaseCoreResourceController;
use {{ $repositoryNamespace }}\{{ $repositoryInterface }};
@foreach ($repositoryInfo['interfaces'] as $inf)
use {{ $repositoryNamespace }}\{{ $inf }};
@endforeach
use {{ $modelPath }}\{{ $model }};

class {{ $className }} extends BaseCoreResourceController
{

@foreach ($repositoryInfo['dataMembers'] as $m)
    {{ $m }}
@endforeach

    public function __construct({{ $repositoryInterface }} ${{ $repositoryInterfaceVar }}{{ $repositoryInfo['params'] }})
    {
        $this->repository = ${{ $repositoryInterfaceVar }};
@foreach ($repositoryInfo['assignments'] as $assignment)
        {!! $assignment !!}
@endforeach
    }

    protected function getModel()
    {
        if (!$this->model){
            $this->model = new {{ $model }}();
        }
        return $this->model;
    }

    protected function getResourceDirectory()
    {
        return '{{ str_singular(snake_case($model)) }}';
    }

    protected function getBaseRoute()
    {
        return 'admin.{{ str_replace('_', '-', str_plural(snake_case($model))) }}';
    }


}