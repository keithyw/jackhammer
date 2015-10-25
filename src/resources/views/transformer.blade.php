{!! $header !!}
/**
 * Created by PhpStorm.
 * User: keithwatanabe
 * Date: 10/19/15
 * Time: 11:24 AM
 */

namespace {{ $namespace }};

use {{ $modelPath }}\{{ $model }};
use League\Fractal\TransformerAbstract;

class {{ $classname }} extends TransformerAbstract
{
    public function transform({{ $model }} ${{ camel_case($model) }})
    {
        return [
            'id' => (int)${{ camel_case($model) }}->id,
@foreach ($attributes as $attr)
            '{{ $attr }}' => ${{ camel_case($model) }}->{{ $attr }},
@endforeach
        ];
    }
}