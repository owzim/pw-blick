<?php

namespace owzim\Blick;

if (PROCESSWIRE >= 300) {
    class BlickWireData extends \ProcessWire\WireData {}
} else {
    class BlickWireData extends \WireData {}
}