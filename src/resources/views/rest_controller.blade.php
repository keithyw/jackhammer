{!! $header !!}

namespace {{ $namespace }};

use Conark\Jackhammer\Http\Controllers\RestCoreController;
use {{ $repositoryNamespace }}\{{ $repositoryInterface }};
use {{ $transformPath }}\{{ $model }}Transformer;
use League\Fractal\Manager;
use League\Fractal\Serializer\JsonApiSerializer;

class {{ $className }} extends RestCoreController
{
    public function __construct(Manager $manager, {{ $repositoryInterface }} ${{ $repositoryInterfaceVar }})
    {
        $this->repository = ${{ $repositoryInterfaceVar }};
        $this->manager = $manager;
        $this->manager->setSerializer(new JsonApiSerializer());
    }

    protected function getTransformer(){
        return new {{ $model }}Transformer();
    }
}