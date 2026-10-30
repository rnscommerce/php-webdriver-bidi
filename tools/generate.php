<?php

declare(strict_types=1);

use Rnscommerce\WebDriver\BiDi\Generator\Generator;

require __DIR__ . '/../vendor/autoload.php';

$root = \dirname(__DIR__);
$schema = \json_decode((string) \file_get_contents("{$root}/schema/schema.json"), true, flags: \JSON_THROW_ON_ERROR);
/** @var array{commands: list<string>, events: list<string>} $allowlist */
$allowlist = require __DIR__ . '/generator/allowlist.php';
if (!\is_array($schema)) {
    throw new UnexpectedValueException('schema/schema.json is not an object');
}
/** @var array<string, mixed> $schema */
$files = (new Generator($schema, $allowlist))->generate();

foreach ([...\glob("{$root}/src/*/*.php") ?: [], ...\glob("{$root}/src/*/*/*.php") ?: []] as $path) {
    if (\str_contains((string) \file_get_contents($path), Generator::HEADER)) {
        \unlink($path);
    }
}
foreach ($files as $path => $contents) {
    $target = "{$root}/src/{$path}";
    if (!\is_dir(\dirname($target))) {
        \mkdir(\dirname($target), 0777, true);
    }
    \file_put_contents($target, $contents);
}
