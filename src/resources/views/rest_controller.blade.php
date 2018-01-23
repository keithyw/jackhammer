{!! $header !!}

namespace {{ $namespace }};

use Jackhammer\Http\Controllers\RestCoreController;
use {{ $repositoryNamespace }}\{{ $repositoryInterface }};
use {{ $formRequestNamespace }}\{{ $model }}FormRequest;
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

    public function store({{ $model }}FormRequest ${{ lcfirst($model) }}FormRequest)
    {
        return $this->_store(${{ lcfirst($model) }}FormRequest->all());
    }

    public function update({{ $model }}FormRequest ${{ lcfirst($model) }}FormRequest, $id)
    {
        return $this->_update($id, ${{ lcfirst($model) }}FormRequest->all());
    }
}