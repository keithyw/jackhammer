{!! $header !!}

namespace {{ $namespace }};

use Conark\Jackhammer\Http\Controllers\BaseCoreResourceController;
use {{ $repositoryNamespace }}\{{ $repositoryInterface }};
use {{ $modelPath }}\{{ $model }};

class {{ $className }} extends BaseCoreResourceController
{


    public function __construct({{ $repositoryInterface }} ${{ $repositoryInterfaceVar }})
    {
        $this->repository = ${{ $repositoryInterfaceVar }};
    }

    protected function getModel()
    {
        return new {{ $model }}();
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