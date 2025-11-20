<?php

namespace MODX\CLI\Tests\Fixtures;

class XdomModxStub
{
    public function toJSON($data)
    {
        return json_encode($data);
    }
}

// Register global alias for tests
if (!class_exists('modX')) {
    class_alias(__NAMESPACE__ . '\\XdomModxStub', 'modX');
}
