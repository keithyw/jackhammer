{!! $header !!}

namespace {{ $namespace }};

use Jackhammer\Http\Controllers\RestCoreController;
use {{ $repositoryNamespace }}\{{ $repositoryInterface }};
use {{ $transformPath }}\{{ $model }}Transformer;
use League\Fractal\Manager;
use League\Fractal\Serializer\JsonApiSerializer;

class {{ $className }} extends RestCoreController
{
    public function __construct(Manager $manager, {{ $repositoryInterface }} ${{ lcfirst($repositoryInterface) }})
    {
        $this->repository = ${{ lcfirst($repositoryInterface) }};
        $this->manager = $manager;
        $this->manager->setSerializer(new JsonApiSerializer());
    }

    protected function getTransformer(){
        return new {{ $model }}Transformer();
    }
}