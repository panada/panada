<?php
namespace Panada\Session\Interfaces;

/**
 * Interface for Session Drivers
 *
 * @package  Session
 * @author   Iskandar Soesman <k4ndar@yahoo.com>
 * @link     http://panadaframework.com/
 * @license  http://www.opensource.org/licenses/bsd-license.php
 * @since    version 1.0.0
 */
interface Session
{
    public function setValue($name, $value = '');
    public function getValue($name);
    public function getAllValue();
    public function deleteValue($name);
    public function regenerateId();
    public function destroy();
}
