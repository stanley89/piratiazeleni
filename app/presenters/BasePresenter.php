<?php

namespace App\Presenters;

use Nette,
	App\Model;


/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{
    protected $kategorie;

    public function injectKategorie(\Models\Kategorie $kategorie) {
        $this->kategorie = $kategorie;
    }

    protected $clanky;

    public function injectClanky(\Models\Clanky $clanky) {
        $this->clanky = $clanky;
    }

    protected function createComponentHledani() {
        $form = new \Nette\Application\UI\Form();
        $form->addText("keyword", "Klíčové slovo");
        $form->onSuccess[] = $this->hledej;
        return $form;
    }
    public function hledej($form) {
        $vals = $form->getValues();
        $this->redirect("Homepage:default", array("search" => $vals['keyword']));
    }
    public function startup() {
        parent::startup();

        $classReflection = new Nette\Reflection\ClassType('Helpers');
        $methods = $classReflection->getMethods();
        foreach ($methods as $method) {
            $this->template->registerHelper($method->getName(), 'Helpers::'.$method->getName());
        }
        //$this->template->kategorie = $this->kategorie->getMenu();
        //$this->template->aktuality = $this->clanky->getClanky(5, 0,"aktuality",null,true);

    }

}
