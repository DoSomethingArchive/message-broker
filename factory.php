<?php

require_once __DIR__ . '/vendor/autoload.php';

/*
 * The Factory Method pattern is a design pattern used to define a runtime
 * interface for creating an object. ItÕs called a factory because it creates
 * various types of objects without necessarily knowing what kind of object it
 * creates or how to create it.
 *
 * http://www.sitepoint.com/understanding-the-factory-method-design-pattern
 * http://stackoverflow.com/questions/2083424/what-is-a-factory-design-pattern-in-php
 */
class ProducerFactory {

   public static function build($type) {
        // assumes the use of an autoloader
        $producer = "Producer_" . $type;
        if (class_exists($producer)) {
            return new $producer();
        }
        else {
            throw new Exception("Invalid producer type given.");
        }
    } 

}

class Producer_OldWorld
{
    private $merge_vars;
 
    public function __construct() {
        // User first name
        $this->addMergeVar(ProducerFactory::build("FNAME"));
        // Current active campaigns  
        $this->addMergeVar(ProducerFactory::build("CAMPAIGNS"));
    }

}



$mySignup = ProducerFactory::build("OldWorld");