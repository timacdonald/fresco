<?php

use Alfresco\Render\Factory;
use Illuminate\Config\Repository as Configuration;
use Illuminate\Container\Container;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Translation\FileLoader;
use Illuminate\Translation\Translator;
use Spatie\ShikiPhp\Shiki;

return tap(Container::getInstance(), function (Container $container) {
    $container->instance(Container::class, $container);

    $container->singleton(Configuration::class, fn () => new Configuration([
        'debug' => false,
        'language' => 'en',
        'root_directory' => __DIR__,
        'build_directory' => __DIR__.'/build/output',
        'cache_directory' => __DIR__.'/build/cache',
        'resource_directory' => __DIR__.'/resources',
        'index_directory' => __DIR__.'/build/indexes',
        'component_directory' => __DIR__.'/resources/components',
        'translation_directory' => __DIR__.'/resources/translations',
        'replacements_directory' => __DIR__.'/resources/replacements',
    ]));

    $container->singleton(Factory::class);

    $container->bind(Shiki::class, fn (Container $container) => new Shiki(
        $container->make(Configuration::class)->get('root_directory').'/theme.json'
    ));

    $container->bind(FileLoader::class, fn (Container $container) => new FileLoader(
        $container->make(Filesystem::class),
        $container->make(Configuration::class)->get('translation_directory'),
    ));

    $container->singleton(Translator::class, fn (Container $container) => new Translator(
        $container->make(FileLoader::class),
        'en',
    ));
});
