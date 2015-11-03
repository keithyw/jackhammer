{!! $header !!}

namespace {{ $namespace }};

use Conark\Jackhammer\Http\Controllers\BaseCoreResourceController;
use {{ $repositoryNamespace }}\{{ $repositoryInterface }};
@foreach ($repositoryInfo['interfaces'] as $inf)
use {{ $repositoryNamespace }}\{{ $inf }};
@endforeach
use {{ $modelPath }}\{{ $model }};
@if (isset($policy))
use {{ $policyPath }}\{{ $policy }};
@endif

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

@if (isset($policy))
    protected function getPolicy()
    {
        if (!$this->policy){
            $this->policy = new {{ $policy }}();
        }
        return $this->policy;
    }
@endif

    protected function getResourceDirectory()
    {
        return '{{ str_singular(snake_case($model)) }}';
    }

    protected function getBaseRoute()
    {
        return 'admin.{{ str_replace('_', '-', str_plural(snake_case($model))) }}';
    }


}