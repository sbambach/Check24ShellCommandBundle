<?php
/**
 * ParameterInterface.php
 *
 * @author    Eugen Ganshorn <eugen.ganshorn@check24.de>
 * @copyright 2017 CHECK24 Vergleichsportal Shopping GmbH <http://preisvergleich.check24.de>
 */

namespace Shopping\ShellCommandBundle\Utils\Command;

interface ParameterInterface
{
    public function addParameter(string $key, string $value);
    public function setParamteters(array $parameters);
    public function getParameters(): array;
}
