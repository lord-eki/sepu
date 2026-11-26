<?php

use Illuminate\Support\Facades\File;

it('autoloads every class declared under app', function () {
    $broken = [];

    foreach (File::allFiles(app_path()) as $file) {
        $source = $file->getContents();

        if (! preg_match('/^namespace\s+([^;]+);/m', $source, $namespace)) {
            continue;
        }

        if (! preg_match('/^(?:final\s+|abstract\s+|readonly\s+)*(?:class|interface|trait|enum)\s+(\w+)/m', $source, $declared)) {
            continue;
        }

        $name = $namespace[1] . '\\' . $declared[1];

        if (! class_exists($name) && ! interface_exists($name) && ! trait_exists($name) && ! enum_exists($name)) {
            $broken[] = $file->getRelativePathname() . ' declares ' . $name;
        }
    }

    expect($broken)->toBe([]);
})->group('audit');