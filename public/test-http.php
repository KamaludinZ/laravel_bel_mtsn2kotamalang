<?php
// Test Laravel HTTP Request handling
echo "Testing Laravel HTTP Request...<br><br>";

try {
    require __DIR__ . '/../vendor/autoload.php';
    $app = require_once __DIR__ . '/../bootstrap/app.php';

    echo "✓ App bootstrapped<br>";

    // Create HTTP Kernel
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    echo "✓ HTTP Kernel created<br>";

    // Create request
    $request = Illuminate\Http\Request::capture();
    echo "✓ Request captured<br>";
    echo "Request URI: " . $request->getRequestUri() . "<br>";
    echo "Request Method: " . $request->getMethod() . "<br><br>";

    // Try to handle request
    echo "Attempting to handle request...<br>";
    $response = $kernel->handle($request);
    echo "✓ Request handled successfully!<br>";
    echo "Response Status: " . $response->getStatusCode() . "<br>";

    // Don't send response, just show it worked
    $kernel->terminate($request, $response);

    echo "<br><b>SUCCESS: Laravel HTTP is working!</b>";

} catch (Throwable $e) {
    echo "<br><b>ERROR:</b> " . $e->getMessage() . "<br>";
    echo "<b>File:</b> " . $e->getFile() . ":" . $e->getLine() . "<br>";
    echo "<b>Class:</b> " . get_class($e) . "<br><br>";
    echo "<b>Stack Trace:</b><br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
