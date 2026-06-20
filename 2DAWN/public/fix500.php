<?php
/**
 * fix500.php v8 – Recursively fixes permissions across all directories,
 * generates missing APP_KEY, removes duplicate routes, clears cache, and boots Laravel.
 * Upload to public/ folder. Visit yourdomain.com/fix500.php
 * DELETE when done!
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');
set_time_limit(240); // 4 minutes

$base = dirname(__DIR__);
$logFile = $base . '/storage/logs/laravel.log';
$envFile = $base . '/.env';

echo "<html><head><title>2DAWN Fixer v8</title>";
echo "<style>body{font-family:monospace;background:#111;color:#0f0;padding:20px;} ";
echo "h2{color:#ff0;} .ok{color:#0f0;} .err{color:#f44;} .warn{color:#fa0;} pre{background:#222;padding:10px;overflow:auto;max-height:500px;white-space:pre-wrap;word-wrap:break-word;} a{color:#0f0;font-size:18px;}</style>";
echo "</head><body>";
echo "<h1>2DAWN Fixer v8</h1>";

function recursiveChmod($dir) {
    if (!is_dir($dir)) {
        return [0, 0];
    }
    
    $filesFixed = 0;
    $dirsFixed = 0;
    
    // Fix the target directory itself
    if (@chmod($dir, 0755)) {
        $dirsFixed++;
    }
    
    try {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($iterator as $item) {
            $path = $item->getPathname();
            if ($item->isDir()) {
                if (@chmod($path, 0755)) {
                    $dirsFixed++;
                }
            } else {
                if (@chmod($path, 0644)) {
                    $filesFixed++;
                }
            }
        }
    } catch (\Exception $e) {
        echo "<p class='warn'>Warning iterating $dir: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    
    return [$filesFixed, $dirsFixed];
}

function ensureAppKey($envFile) {
    if (!file_exists($envFile)) {
        $example = dirname($envFile) . '/.env.example';
        if (file_exists($example)) {
            copy($example, $envFile);
            @chmod($envFile, 0644);
            echo "<p class='warn'>Created .env from .env.example</p>";
        } else {
            echo "<p class='err'>❌ .env does not exist and .env.example is missing!</p>";
            return;
        }
    }
    
    @chmod($envFile, 0644);
    $content = file_get_contents($envFile);
    
    // Regex to detect if APP_KEY has a value (base64:... or 32 chars long)
    if (preg_match('/^APP_KEY=(base64:[a-zA-Z0-9+\/={4}]+|[a-zA-Z0-9]{32,})/m', $content, $matches)) {
        $keyVal = trim(str_replace('APP_KEY=', '', $matches[0]));
        if (strlen($keyVal) > 10) {
            echo "<p class='ok'>✅ APP_KEY is already specified in .env.</p>";
            return;
        }
    }
    
    // Generate a fresh key
    $newKey = 'base64:' . base64_encode(random_bytes(32));
    
    if (preg_match('/^APP_KEY=/m', $content)) {
        $content = preg_replace('/^APP_KEY=.*/m', 'APP_KEY=' . $newKey, $content);
    } else {
        $content .= "\nAPP_KEY=" . $newKey . "\n";
    }
    
    if (file_put_contents($envFile, $content) !== false) {
        echo "<p class='ok'>🔑 <strong>Successfully generated and saved new APP_KEY:</strong> <code>$newKey</code></p>";
    } else {
        echo "<p class='err'>❌ Failed to write the new APP_KEY to .env! Check .env file permissions.</p>";
    }
}

if (isset($_GET['fix']) && $_GET['fix'] === 'yes') {

    // ── APPLY THE FIX ──────────────────────────────────────────────
    echo "<h2>Step 1: Fixing Permissions Recursive (755 for dirs, 644 for files)...</h2>";

    $dirsToFix = [
        'app' => $base . '/app',
        'config' => $base . '/config',
        'routes' => $base . '/routes',
        'resources' => $base . '/resources',
        'database' => $base . '/database',
        'bootstrap' => $base . '/bootstrap',
        'storage' => $base . '/storage',
        'vendor' => $base . '/vendor'
    ];

    foreach ($dirsToFix as $name => $path) {
        echo "<p>Fixing <strong>$name/</strong>... ";
        list($files, $dirs) = recursiveChmod($path);
        echo "<span class='ok'>Done ($files files, $dirs folders fixed)</span></p>";
    }
    
    // Fix root level files permissions (.env, artisan, composer.json)
    @chmod($envFile, 0644);
    @chmod($base . '/artisan', 0755);
    @chmod($base . '/composer.json', 0644);
    @chmod($base . '/composer.lock', 0644);
    echo "<p class='ok'>Fixed permissions on root-level configuration files.</p>";

    echo "<h2>Step 2: Checking / Generating APP_KEY...</h2>";
    ensureAppKey($envFile);

    echo "<h2>Step 3: Cleaning up web.php...</h2>";
    $webFile = $base . '/routes/web.php';
    if (file_exists($webFile)) {
        copy($webFile, $webFile . '.bak.' . date('YmdHis'));
        echo "<p class='ok'>Backed up old web.php</p>";

        $content = file_get_contents($webFile);
        
        // Remove duplicate pricing route (line 36 area)
        $content = preg_replace(
            '/\/\/ Pricing page\s*\nRoute::view\(\'\/pricing\',\s*\'pricing\'\)->name\(\'pricing\'\);\s*\n/',
            "// Pricing page\n\n",
            $content,
            1
        );
        
        file_put_contents($webFile, $content);
        echo "<p class='ok'>Removed duplicate pricing route from web.php.</p>";
    } else {
        echo "<p class='err'>routes/web.php not found at $webFile!</p>";
    }

    // Clear the log so we get fresh errors
    if (file_exists($logFile)) {
        file_put_contents($logFile, '');
    }

    // Delete all cache files
    echo "<h2>Step 4: Clearing Laravel Cache...</h2>";
    $cacheDirs = [
        $base . '/bootstrap/cache',
        $base . '/storage/framework/views',
    ];
    foreach ($cacheDirs as $dir) {
        if (is_dir($dir)) {
            foreach (glob($dir . '/*.php') as $f) { 
                @unlink($f); 
            }
        }
    }
    $fileCacheDir = $base . '/storage/framework/cache/data';
    if (is_dir($fileCacheDir)) {
        try {
            $it = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($fileCacheDir, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::CHILD_FIRST
            );
            foreach ($it as $item) {
                if ($item->isFile() && $item->getFilename() !== '.gitignore') {
                    @unlink($item->getPathname());
                }
            }
        } catch (\Exception $e) {
            // ignore cache clear errors
        }
    }
    echo "<p class='ok'>Cleared bootstrap/cache, view cache, and data cache.</p>";

    // Now boot Laravel and try to load routes
    echo "<h2>Step 5: Booting Laravel & Testing Routes...</h2>";
    
    try {
        require $base . '/vendor/autoload.php';
        $app = require_once $base . '/bootstrap/app.php';
        $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
        $request = Illuminate\Http\Request::create('/', 'GET');
        
        ob_start();
        try {
            $response = $kernel->handle($request);
            ob_end_clean();
            
            $status = $response->getStatusCode();
            echo "<p>Status: <strong>$status</strong></p>";
            
            $router = $app->make('router');
            $routes = $router->getRoutes();
            
            $checks = ['home', 'events.index', 'events.show', 'login', 'organizer.login'];
            foreach ($checks as $routeName) {
                $route = $routes->getByName($routeName);
                if ($route) {
                    echo "<p class='ok'>✅ Route '$routeName' is registered!</p>";
                } else {
                    echo "<p class='err'>❌ Route '$routeName' is missing</p>";
                }
            }
            
            $totalRoutes = count($routes->getRoutes());
            echo "<p>Total routes registered: <strong>$totalRoutes</strong></p>";
            
            if ($status >= 500) {
                echo "<h2>Fresh error from log:</h2>";
                if (file_exists($logFile) && filesize($logFile) > 0) {
                    $lines = file($logFile, FILE_IGNORE_NEW_LINES);
                    $inError = false;
                    $errorBlock = [];
                    foreach ($lines as $line) {
                        if (strpos($line, 'ERROR') !== false) {
                            $inError = true;
                            $errorBlock = [$line];
                        } elseif ($inError) {
                            $errorBlock[] = $line;
                            if (count($errorBlock) > 20) break;
                        }
                    }
                    echo "<pre class='err'>";
                    foreach ($errorBlock as $l) {
                        echo htmlspecialchars(substr($l, 0, 400)) . "\n";
                    }
                    echo "</pre>";
                }
            }
            
        } catch (\Throwable $e) {
            ob_end_clean();
            echo "<p class='err'>Request execution failed: " . htmlspecialchars($e->getMessage()) . "</p>";
            echo "<p class='warn'>File: " . htmlspecialchars($e->getFile()) . ":" . $e->getLine() . "</p>";
            
            // Show stack trace
            echo "<h3>Stack Trace:</h3>";
            echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
        }
    } catch (\Throwable $e) {
        echo "<p class='err'>Boot error: " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<h3>Stack Trace:</h3>";
        echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    }

    echo "<hr><p class='warn'>DELETE this file when done!</p>";
    
} else {

    // ── DIAGNOSE ───────────────────────────────────────────────────
    echo "<h2>Diagnosing environment status...</h2>";
    if (file_exists($envFile)) {
        @chmod($envFile, 0644);
        $content = file_get_contents($envFile);
        if (preg_match('/^APP_KEY=(.*)/m', $content, $m)) {
            $val = trim($m[1]);
            if (empty($val)) {
                echo "<p class='err'>❌ APP_KEY is empty in .env!</p>";
            } else {
                echo "<p class='ok'>✅ APP_KEY is set: <code>" . htmlspecialchars(substr($val, 0, 15)) . "...</code> (" . strlen($val) . " chars)</p>";
            }
        } else {
            echo "<p class='err'>❌ APP_KEY is missing from .env!</p>";
        }
    } else {
        echo "<p class='err'>❌ .env file does not exist at $envFile</p>";
    }

    echo "<h2>Step 2: Getting fresh error...</h2>";
    if (file_exists($logFile)) {
        file_put_contents($logFile, '');
    }
    
    try {
        require $base . '/vendor/autoload.php';
        $app = require_once $base . '/bootstrap/app.php';
        $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
        $request = Illuminate\Http\Request::create('/', 'GET');
        
        ob_start();
        try {
            $response = $kernel->handle($request);
            ob_end_clean();
            echo "<p>Status: " . $response->getStatusCode() . "</p>";
        } catch (\Throwable $e) {
            ob_end_clean();
            echo "<p class='err'>Request error: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    } catch (\Throwable $e) {
        echo "<p class='err'>Boot error: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    
    echo "<h2>Step 3: The REAL error (from fresh log)</h2>";
    if (file_exists($logFile) && filesize($logFile) > 0) {
        $allLog = file_get_contents($logFile);
        $pos = strpos($allLog, 'ERROR');
        if ($pos !== false) {
            $start = max(0, $pos - 50);
            $excerpt = substr($allLog, $start, 2500);
            echo "<pre class='err'>" . htmlspecialchars($excerpt) . "</pre>";
        } else {
            echo "<p class='warn'>No ERROR in log. Showing full log:</p>";
            echo "<pre>" . htmlspecialchars(substr($allLog, 0, 3000)) . "</pre>";
        }
    } else {
        echo "<p class='warn'>Log is empty — the error might not be logged.</p>";
    }
    
    echo "<hr>";
    echo "<p><a href='?fix=yes' style='font-weight:bold;color:#ff0;font-size:22px;'>>>> CLICK HERE TO APPLY APP_KEY, PERMISSION & CACHE FIX <<<</a></p>";
    echo "<p class='warn'>DELETE this file when done!</p>";
}

echo "</body></html>";
