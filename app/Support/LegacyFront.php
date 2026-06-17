<?php

namespace App\Support;

use Illuminate\Http\Response;

final class LegacyFront
{
    public function root(): string
    {
        return ProjectPaths::root();
    }

    /**
     * @param  array<string, scalar|null>  $query
     */
    public function render(string $script, array $query = [], int $status = 200): Response
    {
        $root = $this->root();
        $path = $root.DIRECTORY_SEPARATOR.ltrim($script, '/\\');

        if (! is_file($path)) {
            abort(404);
        }

        $previousGet = $_GET;

        foreach ($query as $key => $value) {
            if ($value !== null && $value !== '') {
                $_GET[$key] = is_scalar($value) ? (string) $value : $value;
            }
        }

        $previousCwd = getcwd() ?: $root;
        chdir($root);

        ob_start();

        try {
            include $path;
            $body = ob_get_clean() ?: '';
        } catch (\Throwable $e) {
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
            chdir($previousCwd);
            $_GET = $previousGet;

            throw $e;
        }

        chdir($previousCwd);
        $_GET = $previousGet;

        return response($body, $status);
    }
}
