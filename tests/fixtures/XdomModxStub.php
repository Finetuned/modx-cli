<?php

namespace MODX\CLI\Tests\Fixtures;

class XdomModxStub
{
    public function toJSON($data)
    {
        return json_encode($data);
    }

    public static function registerAlias(): void
    {
        if (!class_exists('modX')) {
            class_alias(__NAMESPACE__ . '\\XdomModxStub', 'modX');
        }
    }
}
