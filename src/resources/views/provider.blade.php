{!! $header !!}

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register()
    {
        /** start here **/
@foreach ($repositories as $repository)
        $this->app->bind('{{$repository['interface']}}', '{{$repository['repository']}}');
@endforeach
    }
}