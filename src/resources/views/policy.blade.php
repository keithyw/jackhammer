{!! $header !!}

namespace {{ $namespace }};

use {{ $modelNamespace }}\{{ $model }};
use {{ $modelNamespace }}\User;

class {{ $className }}
{
@if ($hasDelete)
    public function delete(User $user, {{ $model }} {{ $modelVar }})
    {
        return $user->id === {{ $modelVar }}->user_id;
    }
@endif

@if ($hasUpdate)
    public function update(User $user, {{ $model }} {{ $modelVar }})
    {
        return $user->id === {{ $modelVar }}->user_id;
    }
@endif
}