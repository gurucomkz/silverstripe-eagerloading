<?php

namespace Gurucomkz\EagerLoading\Tests;

use Gurucomkz\EagerLoading\Utils;
use SilverStripe\Dev\SapphireTest;

class UtilsTest extends SapphireTest
{
    public function testEnsureArray_AlreadyArray()
    {
        $in = [1, 2, 3];
        $out = Utils::EnsureArray($in);

        $this->assertEquals($in, $out);
    }

    public function testEnsureArray_Object()
    {
        $o1 = (object) [
            'id' => 123,
            'title' => 'title1',
        ];
        $o2 = (object) [
            'id' => 234,
            'title' => 'title2',
        ];
        $o3 = (object) [
            'id' => 345,
            'title' => 'title3',
        ];
        $in = (object) [$o1, $o2, $o3];
        $out = Utils::EnsureArray($in, 'id');

        $expect = [
            123 => $o1,
            234 => $o2,
            345 => $o3,
        ];
        $this->assertEquals($expect, $out);
    }
}
