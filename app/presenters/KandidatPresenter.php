<?php

namespace App\Presenters;

use Nette,
    App\Model;
use Nette\Application\UI\Form;

/**
 * Kandidati presenter.
 */
class KandidatPresenter extends BasePresenter
{
    /** @var Models/Kandidati */
    private $kandidati;

    /** @var Models/Upload */
    private $upload;
    
    public function injectUpload(\Models\Upload $upload)
    {
        $this->upload = $upload;
    }

    public function injectKandidati(\Models\Kandidati $kandidati)
    {
        $this->kandidati = $kandidati;
    }
    
    public function renderDefault($url) {
        $kandidat = $this->kandidati->getByUrl($url);
        if (empty($kandidat)) throw new \Nette\Application\BadRequestException;
        $this->template->kandidat = $kandidat;
		$this->template->title = $kandidat['jmeno'];
    }
}
