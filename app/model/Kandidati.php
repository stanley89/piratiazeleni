<?php

namespace Models;

use Nette,
    Nette\Utils\Strings;


/**
 * Category management.
 */
class Kandidati extends \Nette\Object
{
    /** @var Nette\Database\Context */
    private $database;


    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    public function getAll() {
        return $this->database->fetchAll("SELECT * FROM kandidati ORDER by pozice asc;");
    }
    
    public function getByUrl($url) {
        return $this->database->fetch("SELECT * FROM kandidati WHERE url=?;",$url);
    }

}

