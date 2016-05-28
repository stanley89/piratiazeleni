<?php

namespace App\Presenters;

use Nette,
    App\Model;
use Nette\Application\UI\Form;

/**
 * Kandidati presenter.
 */
class KandidatiPresenter extends BasePresenter
{
    /** @var Models/Kandidati */
    private $kandidati;

    public function injectUpload(\Models\Kandidati $kandidati)
    {
        $this->kandidati = $kandidati;
    }

    public function renderDefault() {
        $this->template->kandidati = $this->kandidati->getAll();
    }
}
