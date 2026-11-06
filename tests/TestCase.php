<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    public function createApplication()
    {
        $app = parent::createApplication();
        $connection = $app['config']->get('database.default');
        $database = $app['config']->get("database.connections.{$connection}.database");
        $isTestDatabase = ($connection === 'sqlite' && $database === ':memory:')
            || (is_string($database) && str_ends_with($database, '_test'));

        if (! $app->environment('testing') || $app->configurationIsCached() || ! $isTestDatabase) {
            throw new \RuntimeException(
                'Testes bloqueados: use ambiente testing, sem cache de configuracao, e SQLite :memory: ou banco com sufixo _test. Nenhuma migration de teste foi iniciada.',
            );
        }

        return $app;
    }
}
