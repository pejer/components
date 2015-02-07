<?php
/**
 * Created by Henrik Pejer ( mr@henrikpejer.com )
 * Date: 2014-02-14 22:42
 */

namespace DHP\components\event;

/**
 * Class Wrapper
 *
 * This acts as an event wrapper - meaning that it takes a class and wrapps it's public calls.
 *
 * This way, we can inject events into a class... very sneaky indeed.
 *
 * This is based upon Phockito : https://github.com/hafriedlander/phockito/blob/master/Phockito.php
 *
 * @package DHP\components\event
 */
class Wrapper
{

    private static $defaults;
    private static $isInterface;

    /**
     * Passed a class as a string to create the mock as, and the class as a string to mock,
     * create the mocking class php and eval it into the current running environment
     *
     * @static
     *
     * @param string $mockedClass - The name of the class (or interface) to create a mock of
     *
     * @throws \RuntimeException
     * @return string The name of the mocker class
     *
     * @SuppressWarnings(PHPMD.EvalExpression)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public static function wrap($mockedClass)
    {
        // Bail if we were passed a classname that doesn't exist
        if (!class_exists($mockedClass) && !interface_exists($mockedClass)) {
            throw new \RuntimeException("Cannot wrap non-existing class {$mockedClass}");
        }
        // Reflect on the mocked class
        $reflect = new \ReflectionClass($mockedClass);

        if ($reflect->isFinal()) {
            user_error("Can't mock final class $mockedClass", E_USER_ERROR);
        }

        // Build up an array of php fragments that make the mocking class definition
        $php = array();

        // Get the namespace & the shortname of the mocked class

        $mockedNamespace = $reflect->getNamespaceName();
        $mockedShortName = $reflect->getShortName();

        // Build the short name of the mocker class based on the mocked classes shortname
        $mockerShortName = $mockedShortName . '_EventWrapper';
        // And build the full class name of the mocker by prepending the namespace if appropriate
        $mockerClass = $mockedNamespace . '\\' . $mockerShortName;

        // If we've already built this test double, just return it
        if (class_exists($mockerClass, false)) {
            return $mockerClass;
        }

        // If the mocked class is in a namespace, the test double goes in the same namespace
        $namespaceDeclaration = $mockedNamespace ? "namespace $mockedNamespace;" : '';

        // The only difference between mocking a class or an interface is how the mocking class extends from the mocked
        $extends = $reflect->isInterface() ? 'implements' : 'extends';

        // Add opening class stanza
        $php[] = <<<EOT
$namespaceDeclaration
class $mockerShortName $extends $mockedShortName {
    public \$__________event;
EOT;

        // And record the defaults at the same time
        self::$defaults[$mockedClass] = array();
        // And whether it's an interface
        self::$isInterface[$mockedClass] = $reflect->isInterface();

        // Step through every method declared on the object
        foreach ($reflect->getMethods() as $method) {
            // Skip private methods. They shouldn't ever be called anyway
            if ($method->isPrivate()) {
                continue;
            }

            // Either skip or throw error on final methods.
            if ($method->isFinal()) {
                continue;
            }

            // Get the modifiers for the function as a string (static, public, etc) -
            // ignore abstract though, all mock methods are concrete
            $modifiers = implode(
                ' ',
                \Reflection::getModifierNames($method->getModifiers() & ~(\ReflectionMethod::IS_ABSTRACT))
            );

            // See if the method is return byRef
            $byRef = $method->returnsReference() ? "&" : "";

            // PHP fragment that is the arguments definition for this method
            $defparams  = array();
            $callparams = array();

            // Array of defaults (sparse numeric)
            self::$defaults[$mockedClass][$method->name] = array();

            foreach ($method->getParameters() as $i => $parameter) {
                // Turn the method arguments into a php fragment that calls a function with them
                $callparams[] = '$' . $parameter->getName();

                // Get the type hint of the parameter
                if ($parameter->isArray()) {
                    $type = 'array ';
                } else {
                    /** @noinspection PhpAssignmentInConditionInspection */
                    if ($parameterClass = $parameter->getClass()) {
                        $type = '\\' . $parameterClass->getName() . ' ';
                    } else {
                        $type = '';
                    }
                }

                try {
                    $defaultValue = $parameter->getDefaultValue();
                } catch (\ReflectionException $e) {
                    $defaultValue = null;
                }

                // Turn the method arguments into a php fragment the defines a function with them,
                // including possibly the by-reference "&" and any default
                $defparams[] =
                    $type .
                    ($parameter->isPassedByReference() ? '&' : '') .
                    '$' . $parameter->getName() .
                    ($parameter->isOptional() ? '=' . var_export($defaultValue, true) : '');

                // Finally cache the default value for matching against later
                if ($parameter->isOptional()) {
                    self::$defaults[$mockedClass][$method->name][$i] = $defaultValue;
                }
            }

            // Turn that array into a comma seperated list
            $defparams = implode(', ', $defparams);

            if ($method->name != '__call' && $method->name != '__toString') {
                $args = '$args = func_get_args();';
                $triggerParams = '';
                preg_match_all("#(\\$[^=]+)#", $defparams, $matches);
                if (count($matches[1]) > 0) {
                    $triggerParams = ', &'.implode(', &', $matches[1]);
                }
                if ($method->name == '__construct') {
                    $defparams = '\DHP\components\event\Event $__________event'.($defparams==''?'':','.$defparams);
                    $args .= '$this->__________event = $__________event;array_shift($args);';

                }
                $php[] = <<<EOT
  $modifiers function $byRef {$method->name}( $defparams ){
    {$args}
    # here a 'before'
    \$this->__________event->triggerSubscribe(\$this, __FUNCTION__, \$args);
    \$trigger = str_replace('\\\\','.',__NAMESPACE__.'.'.__FUNCTION__);
    call_user_func_array(array(\$this->__________event,'trigger'),array('BEFORE:'.\$trigger$triggerParams));
    \$ret = call_user_func_array('parent::{$method->name}', \$args);
    call_user_func_array(array(\$this->__________event,'trigger'),array('AFTER:'.\$trigger$triggerParams,&\$ret));
     # here a 'after'
    return \$ret;
  }
EOT;
            }
        }
        $php[] = <<<EOT
  function __call(\$name, \$args) {
    \$method = "parent::{\$name}";
    if ( !is_callable(\$method) ) {
        \$trace = debug_backtrace();
        \$caller = next(\$trace);
        # \$caller = next(\$caller);
        trigger_error("Call to undefined method {\$name} called from ".\$caller['file'].
            ' on line '.\$caller['line'].''."\\nerror handler", E_USER_ERROR);
        return null;
    }
    return call_user_func_array(\$method, \$args);
  }
EOT;

        $php[] = <<<EOT
  function __toString() {
        \$method = "parent::__toString";
        if ( !is_callable(\$method) ){
            return '';
        } else {
            return parent::__toString();
        }
  }
EOT;

        // Close off the class definition and eval it to create the class as an extant entity.
        $php[] = '}';
        # echo "<pre>\n".implode("\n\n", $php)."</pre>";
        eval(implode("\n\n", $php));
        return $mockerClass;
    }
}
