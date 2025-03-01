<?php

namespace VojtechDobes\NetteForms;

use Nette;
use Nette\DI;
use Nette\PhpGenerator;
use Nette\Schema\Expect;

if (!class_exists('Nette\DI\CompilerExtension')) {
    class_alias('Nette\Config\CompilerExtension', 'Nette\DI\CompilerExtension');
}
if (!class_exists('Nette\PhpGenerator\ClassType')) {
    class_alias('Nette\Utils\PhpGenerator\ClassType', 'Nette\PhpGenerator\ClassType');
}


/**
 * Registers macros and add helpers
 *
 * @author Vojtěch Dobeš
 */
class GpsPickerExtension extends DI\CompilerExtension
{

    public function getConfigSchema() : Nette\Schema\Schema
    {
        return Expect::structure([
            'driver' => Expect::string(GpsPicker::DRIVER_SEZNAM),
            'type' => Expect::string(GpsPicker::TYPE_BASE)->before('strtoupper'),
        ]);
    }

    public function loadConfiguration()
    {
        $container = $this->getContainerBuilder();

        $latte = $container->getDefinition('nette.latteFactory');
        $latte->getResultDefinition()->addSetup('VojtechDobes\NetteForms\GpsPickerMacros::install(?->getCompiler())', array('@self'));
    }



    public function afterCompile(PhpGenerator\ClassType $class)
    {
        $config = $this->getConfig();
        if (in_array($config->type, GpsPicker::$typeSupport[$config->driver]) === FALSE) {
            throw new UnsupportedTypeException("Driver '$config->driver' doesn't support '$config->type' type.");
        }

        $initialize = $class->methods['initialize'];
        $initialize->addBody('VojtechDobes\NetteForms\GpsPositionPicker::register(?, ?);', array(
            $config->driver,
            $config->type,
        ));
    }

}
