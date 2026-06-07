<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Tests\TestCase;

class RegressionSafetyTest extends TestCase
{
    public function test_all_static_route_references_in_project_are_defined(): void
    {
        $undefinedRoutes = [];

        foreach ($this->scannedProjectFiles() as $filePath) {
            $content = file_get_contents($filePath);

            if ($content === false) {
                continue;
            }

            preg_match_all('/(?<!->)(?<!::)\broute\s*\(\s*[\'"]([^\'"]+)[\'"]/', $content, $matches);

            foreach ($matches[1] ?? [] as $routeName) {
                if ($this->shouldIgnoreRouteName($routeName)) {
                    continue;
                }

                if (!Route::has($routeName)) {
                    $undefinedRoutes[] = $this->relativePath($filePath) . ' => route("' . $routeName . '")';
                }
            }
        }

        $this->assertEmpty(
            $undefinedRoutes,
            "Ada route() yang mengarah ke route name tidak terdaftar:\n" . implode("\n", $undefinedRoutes)
        );
    }

    public function test_static_route_references_do_not_contain_whitespace(): void
    {
        $invalidRoutes = [];

        foreach ($this->scannedProjectFiles() as $filePath) {
            $content = file_get_contents($filePath);

            if ($content === false) {
                continue;
            }

            preg_match_all('/(?<!->)(?<!::)\broute\s*\(\s*[\'"]([^\'"]+)[\'"]/', $content, $matches);

            foreach ($matches[1] ?? [] as $routeName) {
                if (preg_match('/\s/', $routeName)) {
                    $invalidRoutes[] = $this->relativePath($filePath) . ' => route("' . $routeName . '")';
                }
            }
        }

        $this->assertEmpty(
            $invalidRoutes,
            "Ada route name yang mengandung spasi/karakter whitespace:\n" . implode("\n", $invalidRoutes)
        );
    }

    public function test_no_nested_feature_test_folder_exists(): void
    {
        $this->assertDirectoryDoesNotExist(
            base_path('tests/Feature/tests'),
            'Folder tests/Feature/tests tidak boleh ada karena menyebabkan warning PSR-4 dan test tidak terbaca dengan benar.'
        );
    }

    public function test_testing_uses_isolated_compiled_view_path(): void
    {
        $compiledPath = str_replace('\\', '/', (string) config('view.compiled'));

        $this->assertStringContainsString(
            'storage/framework/testing/views',
            $compiledPath,
            'Testing harus memakai compiled view khusus agar tidak bentrok dengan storage/framework/views di Windows.'
        );

        $this->assertDirectoryExists(
            config('view.compiled'),
            'Folder compiled view testing belum tersedia.'
        );
    }

    private function scannedProjectFiles(): array
    {
        $directories = [
            base_path('app/Http/Controllers'),
            base_path('resources/views'),
            base_path('routes'),
        ];

        $files = [];

        foreach ($directories as $directory) {
            if (!is_dir($directory)) {
                continue;
            }

            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($directory)
            );

            foreach ($iterator as $fileInfo) {
                if (!$fileInfo->isFile()) {
                    continue;
                }

                $path = $fileInfo->getPathname();

                if (!str_ends_with($path, '.php') && !str_ends_with($path, '.blade.php')) {
                    continue;
                }

                $files[] = $path;
            }
        }

        return $files;
    }

    private function shouldIgnoreRouteName(string $routeName): bool
    {
        return trim($routeName) === '';
    }

    private function relativePath(string $path): string
    {
        return str_replace(
            ['\\', base_path() . DIRECTORY_SEPARATOR],
            ['/', ''],
            $path
        );
    }
}