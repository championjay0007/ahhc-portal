<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$router = $app->make(Illuminate\Routing\Router::class);
$route = $router->getRoutes()->getByName('portal.participant.messages.show');
if (! $route) {
    echo "NO_ROUTE\n";
    exit(0);
}
echo "ROUTE_URI=" . $route->uri() . "\n";
echo "ROUTE_NAME=" . $route->getName() . "\n";
echo "ROUTE_METHODS=" . implode(',', $route->methods()) . "\n";
echo "GENERATED=" . route('portal.participant.messages.show', 4) . "\n";
try {
    $request = Illuminate\Http\Request::create('/portal/participant/messages/4', 'GET');
    $match = $router->getRoutes()->match($request);
    echo "MATCH_NAME=" . $match->getName() . "\n";
    echo "MATCH_ACTION=" . $match->getActionName() . "\n";
} catch (Exception $e) {
    echo "MATCH_EXCEPTION=" . get_class($e) . ': ' . $e->getMessage() . "\n";
}
